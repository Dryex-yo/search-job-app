<?php

namespace App\Services;

use OpenAI\Client;
use Exception;

class CvAnalysisService
{
    private ?Client $client = null;
    private string $model = 'gpt-3.5-turbo';
    private int $maxRetries = 3;
    private int $retryDelay = 5; // seconds
    private bool $apiKeyValid = false;

    public function __construct()
    {
        $apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        
        // Check if API key exists and is not disabled/empty
        if ($apiKey && !in_array($apiKey, ['disabled', ''], true)) {
            try {
                $this->client = \OpenAI::client($apiKey);
                $this->apiKeyValid = true;
            } catch (Exception $e) {
                // API key invalid, fallback will be used
                $this->apiKeyValid = false;
            }
        } else {
            // No API key or disabled
            $this->apiKeyValid = false;
        }
    }

    /**
     * Analyze CV against job description and return match score
     *
     * @param string $cvText Extracted CV text
     * @param string $jobTitle Job title
     * @param string $jobDescription Job description
     * @return array ['score' => 0-100, 'analysis' => 'detailed analysis text']
     * @throws Exception
     */
    public function analyzeMatch(string $cvText, string $jobTitle, string $jobDescription): array
    {
        // If API key is not valid, use fallback immediately
        if (!$this->apiKeyValid || !$this->client) {
            \Illuminate\Support\Facades\Log::info('OpenAI API key not configured, using fallback score', [
                'title' => $jobTitle
            ]);
            
            // Return basic match score based on text similarity
            return $this->getFallbackScore($cvText, $jobTitle, $jobDescription);
        }

        $lastException = null;

        // Retry with exponential backoff
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                // Truncate texts if too long to avoid token limits
                $cvText = $this->truncateText($cvText, 1500);
                $jobDescription = $this->truncateText($jobDescription, 800);

                $prompt = $this->buildPrompt($cvText, $jobTitle, $jobDescription);

                $response = $this->client->chat()->create([
                    'model' => $this->model,
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
                    'temperature' => 0.3,  // Lower temperature for consistent scoring
                    'max_tokens' => 300,  // Reduced for faster response
                ]);

                $responseText = $response->choices[0]->message->content;
                
                // Parse JSON response
                return $this->parseAiResponse($responseText);
            } catch (Exception $e) {
                $lastException = $e;
                $errorMsg = $e->getMessage();

                // Check if it's a rate limit error
                $isRateLimit = stripos($errorMsg, 'rate limit') !== false || 
                              stripos($errorMsg, 'quota') !== false ||
                              stripos($errorMsg, '429') !== false;

                \Illuminate\Support\Facades\Log::warning("AI analysis attempt {$attempt} failed", [
                    'error' => $errorMsg,
                    'attempt' => $attempt,
                    'is_rate_limit' => $isRateLimit
                ]);

                // If rate limit and not last attempt, wait and retry
                if ($isRateLimit && $attempt < $this->maxRetries) {
                    $delay = $this->retryDelay * pow(2, $attempt - 1); // Exponential backoff
                    sleep($delay);
                    continue;
                }

                // If it's the last attempt and it's a rate limit, use fallback
                if ($attempt === $this->maxRetries && $isRateLimit) {
                    \Illuminate\Support\Facades\Log::warning('OpenAI rate limit exceeded, using fallback score', [
                        'title' => $jobTitle,
                        'error' => $errorMsg
                    ]);
                    
                    // Return basic match score based on text similarity
                    return $this->getFallbackScore($cvText, $jobTitle, $jobDescription);
                }

                // For other errors, throw exception
                throw new Exception("AI analysis failed: " . $errorMsg);
            }
        }

        // Final fallback
        throw new Exception("AI analysis failed: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    }

    /**
     * Build the prompt for AI analysis
     *
     * @param string $cvText CV text
     * @param string $jobTitle Job title
     * @param string $jobDescription Job description
     * @return string Prompt for AI
     */
    private function buildPrompt(string $cvText, string $jobTitle, string $jobDescription): string
    {
        return <<<PROMPT
Score CV match for: $jobTitle

**CV:**
$cvText

**Job:**
$jobDescription

Return JSON:
{
  "score": <0-100>,
  "matching_skills": [list top 3],
  "missing_skills": [list top 2],
  "summary": "1-2 sentence fit analysis"
}
PROMPT;
    }

    /**
     * Parse AI response and extract score and analysis
     *
     * @param string $response AI response text
     * @return array ['score' => number, 'analysis' => string]
     * @throws Exception
     */
    private function parseAiResponse(string $response): array
    {
        try {
            // Extract JSON from response (in case there's extra text)
            if (preg_match('/\{.*\}/s', $response, $matches)) {
                $response = $matches[0];
            }

            $data = json_decode($response, true);

            if (!isset($data['score']) || !is_numeric($data['score'])) {
                throw new Exception("Invalid response format: missing score");
            }

            $score = intval($data['score']);
            $score = max(0, min(100, $score)); // Ensure score is 0-100

            $analysis = $this->buildAnalysisSummary($data);

            return [
                'score' => $score,
                'analysis' => $analysis
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to parse AI response: " . $e->getMessage());
        }
    }

    /**
     * Build a summary analysis from AI response
     *
     * @param array $data Parsed AI response data
     * @return string Summary text
     */
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

    /**
     * Truncate text to specified character limit
     *
     * @param string $text Text to truncate
     * @param int $limit Character limit
     * @return string Truncated text
     */
    private function truncateText(string $text, int $limit): string
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit) . '...';
    }

    /**
     * Get fallback score when OpenAI API fails (rate limit, quota exceeded)
     * Uses simple text similarity matching
     *
     * @param string $cvText CV text
     * @param string $jobTitle Job title
     * @param string $jobDescription Job description
     * @return array ['score' => 0-100, 'analysis' => 'text']
     */
    private function getFallbackScore(string $cvText, string $jobTitle, string $jobDescription): array
    {
        // Convert to lowercase for comparison
        $cvLower = strtolower($cvText);
        $jobLower = strtolower($jobDescription);
        $titleLower = strtolower($jobTitle);

        // Extract common keywords from job description
        $jobKeywords = $this->extractKeywords($jobLower);
        
        // Count matches in CV
        $matchCount = 0;
        foreach ($jobKeywords as $keyword) {
            if (stripos($cvLower, $keyword) !== false) {
                $matchCount++;
            }
        }

        // Calculate percentage match
        $score = $jobKeywords ? (int) (($matchCount / count($jobKeywords)) * 100) : 50;
        $score = max(0, min(100, $score)); // Ensure 0-100

        $analysis = "Fallback Score (OpenAI unavailable)\n\n";
        $analysis .= "Based on keyword matching: {$score}% match\n\n";
        $analysis .= "Job: " . $titleLower . "\n";
        $analysis .= "Keywords matched: {$matchCount} / " . count($jobKeywords);

        return [
            'score' => $score,
            'analysis' => $analysis
        ];
    }

    /**
     * Extract keywords from text (simple implementation)
     *
     * @param string $text Text to extract from
     * @return array Keywords
     */
    private function extractKeywords(string $text): array
    {
        // Split by common word boundaries
        $words = preg_split('/[\s,\.\-\/]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter common words and short words
        $keywords = array_filter($words, function($word) {
            $common = ['the', 'a', 'an', 'and', 'or', 'of', 'in', 'to', 'for', 'is', 'are', 'be'];
            return strlen($word) > 2 && !in_array($word, $common);
        });

        // Return unique keywords, limit to 20
        return array_slice(array_unique($keywords), 0, 20);
    }
}
