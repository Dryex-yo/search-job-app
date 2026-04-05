<?php

namespace App\Services;

use OpenAI\Client as OpenAIClient;
use Exception;

class CvAnalysisService
{
    private ?OpenAIClient $openaiClient = null;
    private ?string $geminiApiKey = null;
    private string $geminiModel = 'gemini-2.0-flash';
    private int $maxRetries = 3;
    private int $retryDelay = 5;
    private string $selectedProvider = 'none';
    private array $availableProviders = [];

    public function __construct()
    {
        $openaiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $geminiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');

        if ($openaiKey && !in_array($openaiKey, ['disabled', ''], true)) {
            try {
                $this->openaiClient = \OpenAI::client($openaiKey);
                $this->availableProviders['openai'] = true;
            } catch (Exception $e) {
                $this->availableProviders['openai'] = false;
            }
        } else {
            $this->availableProviders['openai'] = false;
        }

        if ($geminiKey && !in_array($geminiKey, ['disabled', ''], true)) {
            $this->geminiApiKey = $geminiKey;
            $this->geminiModel = config('services.gemini.model', 'gemini-2.0-flash');
            $this->availableProviders['gemini'] = true;
        } else {
            $this->availableProviders['gemini'] = false;
        }

        if ($this->availableProviders['openai']) {
            $this->selectedProvider = 'openai';
        } elseif ($this->availableProviders['gemini']) {
            $this->selectedProvider = 'gemini';
        } else {
            $this->selectedProvider = 'none';
        }

        \Illuminate\Support\Facades\Log::info('CvAnalysisService initialized', [
            'selected_provider' => $this->selectedProvider,
            'available_providers' => $this->availableProviders
        ]);
    }

    public function analyzeMatch(string $cvText, string $jobTitle, string $jobDescription): array
    {
        if ($this->selectedProvider === 'none') {
            \Illuminate\Support\Facades\Log::info('No AI provider configured, using fallback score', [
                'title' => $jobTitle
            ]);
            return $this->getFallbackScore($cvText, $jobTitle, $jobDescription);
        }

        $lastException = null;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $cvTextTruncated = $this->truncateText($cvText, 1500);
                $jobDescriptionTruncated = $this->truncateText($jobDescription, 800);
                $prompt = $this->buildPrompt($cvTextTruncated, $jobTitle, $jobDescriptionTruncated);

                if ($this->selectedProvider === 'openai') {
                    $response = $this->analyzeWithOpenAI($prompt);
                } else {
                    $response = $this->analyzeWithGemini($prompt);
                }

                return $this->parseAiResponse($response);
            } catch (Exception $e) {
                $lastException = $e;
                $errorMsg = $e->getMessage();

                $isRateLimit = stripos($errorMsg, 'rate limit') !== false || 
                              stripos($errorMsg, 'quota') !== false ||
                              stripos($errorMsg, '429') !== false ||
                              stripos($errorMsg, '503') !== false;

                \Illuminate\Support\Facades\Log::warning("AI analysis attempt {$attempt} failed with {$this->selectedProvider}", [
                    'error' => $errorMsg,
                    'attempt' => $attempt,
                    'is_rate_limit' => $isRateLimit,
                ]);

                if ($attempt === 1) {
                    if ($this->selectedProvider === 'openai' && $this->availableProviders['gemini']) {
                        \Illuminate\Support\Facades\Log::info('OpenAI failed, switching to Gemini');
                        $this->selectedProvider = 'gemini';
                        continue;
                    } elseif ($this->selectedProvider === 'gemini' && $this->availableProviders['openai']) {
                        \Illuminate\Support\Facades\Log::info('Gemini failed, switching to OpenAI');
                        $this->selectedProvider = 'openai';
                        continue;
                    }
                }

                if ($isRateLimit && $attempt < $this->maxRetries) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1);
                    sleep($delay);
                    continue;
                }

                if ($attempt === $this->maxRetries && $isRateLimit) {
                    \Illuminate\Support\Facades\Log::warning('All AI providers rate limited, using fallback score');
                    return $this->getFallbackScore($cvText, $jobTitle, $jobDescription);
                }

                throw new Exception("AI analysis failed: " . $errorMsg);
            }
        }

        throw new Exception("AI analysis failed: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    }

    private function analyzeWithOpenAI(string $prompt): string
    {
        if (!$this->openaiClient) {
            throw new Exception("OpenAI client not initialized");
        }

        $response = $this->openaiClient->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an HR analyst. Analyze CV vs job description. Return ONLY valid JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 300,
        ]);

        return $response->choices[0]->message->content;
    }

    private function analyzeWithGemini(string $prompt): string
    {
        if (!$this->geminiApiKey) {
            throw new Exception("Gemini API key not configured");
        }

        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $this->geminiModel . ':generateContent?key=' . urlencode($this->geminiApiKey);

        $data = [
            'contents' => [[
                'parts' => [[
                    'text' => $prompt
                ]]
            ]],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 300,
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Gemini API request failed: " . $error);
        }

        if ($httpCode !== 200) {
            $errorBody = json_decode($response, true);
            $errorMsg = $errorBody['error']['message'] ?? 'Unknown error';
            throw new Exception("Gemini API error (HTTP $httpCode): " . $errorMsg);
        }

        $responseData = json_decode($response, true);
        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Invalid Gemini response format");
        }

        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    }

    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription): string
    {
        return <<<PROMPT
Score CV match for: $jobTitle

**CV:**
$cvText

**Job:**
$jobDescription

Return ONLY valid JSON (no other text):
{
  "score": <0-100>,
  "matching_skills": ["skill1", "skill2", "skill3"],
  "missing_skills": ["skill1", "skill2"],
  "summary": "1-2 sentence fit analysis"
}
PROMPT;
    }

    private function parseAiResponse(string $response): array
    {
        try {
            if (preg_match('/\{.*\}/s', $response, $matches)) {
                $response = $matches[0];
            }

            $data = json_decode($response, true);

            if (!isset($data['score']) || !is_numeric($data['score'])) {
                throw new Exception("Invalid response format: missing score");
            }

            $score = intval($data['score']);
            $score = max(0, min(100, $score));

            $analysis = $this->buildAnalysisSummary($data);

            return ['score' => $score, 'analysis' => $analysis];
        } catch (Exception $e) {
            throw new Exception("Failed to parse AI response: " . $e->getMessage());
        }
    }

    private function buildAnalysisSummary(array $data): string
    {
        $summary = $data['summary'] ?? 'Analysis completed.';
        
        if (isset($data['matching_skills']) && is_array($data['matching_skills'])) {
            $skills = implode(', ', array_slice($data['matching_skills'], 0, 5));
            $summary .= "\n\nMatching Skills: " . $skills;
        }

        if (isset($data['missing_skills']) && is_array($data['missing_skills'])) {
            $missing = implode(', ', array_slice($data['missing_skills'], 0, 3));
            $summary .= "\n\nSkill Gaps: " . $missing;
        }

        return $summary;
    }

    private function truncateText(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }
        return substr($text, 0, $limit) . '...';
    }

    private function getFallbackScore(string $cvText, string $jobTitle, string $jobDescription): array
    {
        $cvLower = strtolower($cvText);
        $jobLower = strtolower($jobDescription);
        $titleLower = strtolower($jobTitle);

        $jobKeywords = $this->extractKeywords($jobLower);
        
        $matchCount = 0;
        foreach ($jobKeywords as $keyword) {
            if (stripos($cvLower, $keyword) !== false) {
                $matchCount++;
            }
        }

        $score = $jobKeywords ? (int) (($matchCount / count($jobKeywords)) * 100) : 50;
        $score = max(0, min(100, $score));

        $analysis = "Fallback Score (Keyword Matching)\n\n";
        $analysis .= "Match Score: {$score}%\n\n";
        $analysis .= "Job Title: " . ucwords(str_replace('_', ' ', $titleLower)) . "\n";
        $analysis .= "Keywords matched: {$matchCount} / " . count($jobKeywords);

        return ['score' => $score, 'analysis' => $analysis];
    }

    private function extractKeywords(string $text): array
    {
        $words = preg_split('/[\s,\.\-\/\+\(\)]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $common = ['the', 'a', 'an', 'and', 'or', 'of', 'in', 'to', 'for', 'is', 'are', 'be', 'at', 'by', 'as', 'with', 'from', 'on', 'have', 'has', 'can', 'will', 'would', 'should', 'could'];
        
        $keywords = array_filter($words, function($word) use ($common) {
            return strlen($word) > 2 && !in_array(strtolower($word), $common);
        });

        return array_slice(array_unique($keywords), 0, 20);
    }
}
