<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Exception;

class PdfExtractionService
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from PDF file
     *
     * @param string $filePath Absolute path to PDF file
     * @return string Extracted text from PDF
     * @throws Exception
     */
    public function extractText(string $filePath): string
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }

            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();

            // Clean up extracted text
            return $this->cleanText($text);
        } catch (Exception $e) {
            throw new Exception("Failed to extract PDF text: " . $e->getMessage());
        }
    }

    /**
     * Clean and normalize extracted text
     *
     * @param string $text Raw extracted text
     * @return string Cleaned text
     */
    private function cleanText(string $text): string
    {
        // Remove extra whitespace and normalize line breaks
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }

    /**
     * Extract text from resume path (that might be a storage path)
     *
     * @param string $resumePath Storage path from database (e.g., 'resumes/file.pdf')
     * @return string Extracted text from PDF
     * @throws Exception
     */
    public function extractFromStoragePath(string $resumePath): string
    {
        try {
            // Normalize path separators to forward slash for consistency
            $resumePath = str_replace('\\', '/', $resumePath);
            
            // Try public disk first (for uploaded resumes)
            $fullPath = storage_path('app/public/' . $resumePath);
            
            // If not found in public, try private disk
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/' . $resumePath);
            }
            
            // Normalize Windows path separators
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
            return $this->extractText($fullPath);
        } catch (Exception $e) {
            throw new Exception("Failed to extract from storage path: " . $e->getMessage());
        }
    }
}
