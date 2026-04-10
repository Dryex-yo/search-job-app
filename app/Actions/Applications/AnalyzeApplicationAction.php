<?php

namespace App\Actions\Applications;

use App\Models\Application;
use App\Services\PdfExtractionService;
use App\Services\CvAnalysisService;
use Exception;
use Illuminate\Support\Facades\Log;

class AnalyzeApplicationAction
{
    private PdfExtractionService $pdfService;
    private CvAnalysisService $analysisService;
    private const MAX_RETRY_ATTEMPTS = 3;

    public function __construct(
        PdfExtractionService $pdfService,
        CvAnalysisService $analysisService
    ) {
        $this->pdfService = $pdfService;
        $this->analysisService = $analysisService;
    }

    /**
     * Execute the AI analysis on an application
     *
     * @param Application $application The application to analyze
     * @return array Result with score and details
     * @throws Exception
     */
    public function execute(Application $application): array
    {
        try {
            // Check if we've exceeded max retry attempts
            $attemptCount = $application->ai_analysis_attempt_count ?? 0;
            if ($attemptCount >= self::MAX_RETRY_ATTEMPTS) {
                throw new Exception("Maximum retry attempts (" . self::MAX_RETRY_ATTEMPTS . ") exceeded. Please contact support.");
            }

            // Update status to analyzing and increment attempt counter
            $application->update([
                'ai_analysis_status' => 'analyzing',
                'ai_analysis_attempt_count' => $attemptCount + 1,
                'ai_analysis_last_attempted_at' => now(),
                'ai_analysis_error_details' => null // Clear previous error details
            ]);

            // Extract CV text
            $cvText = $this->pdfService->extractFromStoragePath($application->resume_path);

            if (empty($cvText)) {
                throw new Exception("Could not extract text from PDF. The file may be corrupted or unreadable.");
            }

            // Get job information
            $job = $application->job;
            if (!$job) {
                throw new Exception("Job not found. This job may have been deleted.");
            }

            // Request AI analysis with timeout handling
            try {
                $analysisResult = $this->analysisService->analyzeMatch(
                    $cvText,
                    $job->title,
                    $job->description ?? ''
                );
            } catch (Exception $analysisException) {
                // Classify the error
                $errorMessage = $analysisException->getMessage();
                $classifiedError = $this->classifyError($errorMessage);
                
                throw new Exception($classifiedError);
            }

            // Update application with results
            $application->update([
                'ai_match_score' => $analysisResult['score'],
                'ai_analysis_details' => $analysisResult['analysis'],
                'ai_analysis_status' => 'completed',
                'ai_analyzed_at' => now(),
                'ai_analysis_error_details' => null // Clear error details on success
            ]);

            Log::info('CV Analysis Completed', [
                'application_id' => $application->id,
                'score' => $analysisResult['score'],
                'analysis' => substr($analysisResult['analysis'], 0, 100),
                'attempt_count' => $attemptCount + 1
            ]);

            return [
                'success' => true,
                'score' => $analysisResult['score'],
                'analysis' => $analysisResult['analysis'],
                'message' => 'Analysis completed successfully'
            ];
        } catch (Exception $e) {
            return $this->handleAnalysisFailure($application, $e);
        }
    }

    /**
     * Handle analysis failure with detailed error tracking
     */
    private function handleAnalysisFailure(Application $application, Exception $e): array
    {
        $errorMessage = $e->getMessage();
        $attemptCount = $application->ai_analysis_attempt_count ?? 0;

        // Determine if this is retriable
        $isRetriable = $attemptCount < self::MAX_RETRY_ATTEMPTS && $this->isRetriableError($errorMessage);

        // Update application with error details
        $application->update([
            'ai_analysis_status' => 'failed',
            'ai_analysis_error_details' => $this->buildDetailedErrorMessage($errorMessage, $attemptCount, $isRetriable)
        ]);

        Log::error('CV Analysis Failed', [
            'application_id' => $application->id,
            'error' => $errorMessage,
            'attempt_count' => $attemptCount,
            'is_retriable' => $isRetriable,
            'trace' => $e->getTraceAsString()
        ]);

        throw new Exception($errorMessage);
    }

    /**
     * Classify error message for better UX
     */
    private function classifyError(string $errorMessage): string
    {
        $message = strtolower($errorMessage);

        // Timeout errors
        if (str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return 'Request timeout. The API service is taking too long to respond. Please retry.';
        }

        // Rate limit errors
        if (str_contains($message, 'rate') || str_contains($message, '429')) {
            return 'Rate limit exceeded. Too many requests to the AI service. Please wait a moment and retry.';
        }

        // API configuration errors
        if (str_contains($message, 'api key') || str_contains($message, 'unauthorized') || str_contains($message, '401')) {
            return 'API configuration error. The AI service is not properly configured. Please contact support.';
        }

        // Service unavailable
        if (str_contains($message, 'unavailable') || str_contains($message, '503') || str_contains($message, 'service')) {
            return 'AI service is temporarily unavailable. Please retry in a few moments.';
        }

        // PDF extraction errors
        if (str_contains($message, 'pdf') || str_contains($message, 'extract')) {
            return 'Could not extract CV text. The PDF file may be corrupted or in an unsupported format.';
        }

        // Generic fallback
        return $errorMessage ?: 'An unknown error occurred during analysis.';
    }

    /**
     * Check if an error is retriable
     */
    private function isRetriableError(string $errorMessage): bool
    {
        $message = strtolower($errorMessage);

        // These errors are retriable
        $retriableErrors = [
            'timeout',
            'rate',
            'temporarily',
            'unavailable',
            'service',
            'connection',
            'retry'
        ];

        foreach ($retriableErrors as $error) {
            if (str_contains($message, $error)) {
                return true;
            }
        }

        // These errors are NOT retriable (permanent failures)
        $permanentErrors = [
            'api key',
            'unauthorized',
            '401',
            'not found',
            'corrupted',
            'invalid pdf'
        ];

        foreach ($permanentErrors as $error) {
            if (str_contains($message, $error)) {
                return false;
            }
        }

        // By default, assume retriable
        return true;
    }

    /**
     * Build detailed error message for display
     */
    private function buildDetailedErrorMessage(string $baseError, int $attemptCount, bool $isRetriable): string
    {
        $message = "❌ Analysis Failed: $baseError\n";
        $message .= "Attempt: $attemptCount/" . self::MAX_RETRY_ATTEMPTS . "\n";
        
        if ($isRetriable) {
            $remainingAttempts = self::MAX_RETRY_ATTEMPTS - $attemptCount;
            $message .= "Status: Retriable ($remainingAttempts retries remaining)\n";
            $message .= "Action: Click 'Retry Analysis' to try again.";
        } else {
            $message .= "Status: Not retriable (permanent error)\n";
            $message .= "Action: Contact support if this persists.";
        }

        return $message;
    }
}
