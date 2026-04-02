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
            // Update status to analyzing
            $application->update([
                'ai_analysis_status' => 'analyzing'
            ]);

            // Extract CV text
            $cvText = $this->pdfService->extractFromStoragePath($application->resume_path);

            if (empty($cvText)) {
                throw new Exception("Could not extract text from PDF");
            }

            // Get job information
            $job = $application->job;
            if (!$job) {
                throw new Exception("Job not found");
            }

            // Request AI analysis
            $analysisResult = $this->analysisService->analyzeMatch(
                $cvText,
                $job->title,
                $job->description ?? ''
            );

            // Update application with results
            $application->update([
                'ai_match_score' => $analysisResult['score'],
                'ai_analysis_details' => $analysisResult['analysis'],
                'ai_analysis_status' => 'completed',
                'ai_analyzed_at' => now()
            ]);

            Log::info('CV Analysis Completed', [
                'application_id' => $application->id,
                'score' => $analysisResult['score'],
                'analysis' => substr($analysisResult['analysis'], 0, 100)
            ]);

            return [
                'success' => true,
                'score' => $analysisResult['score'],
                'analysis' => $analysisResult['analysis'],
                'message' => 'Analysis completed successfully'
            ];
        } catch (Exception $e) {
            // Update status to failed
            $application->update([
                'ai_analysis_status' => 'failed'
            ]);

            Log::error('CV Analysis Failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception("Analysis failed: " . $e->getMessage());
        }
    }
}
