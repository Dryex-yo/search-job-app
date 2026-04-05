<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Gemini API Integration ===\n\n";

try {
    // Initialize service
    $service = new \App\Services\CvAnalysisService();
    echo "✅ CvAnalysisService initialized\n";

    // Test analysis
    echo "\n📝 Running CV analysis...\n";
    $result = $service->analyzeMatch(
        "PHP Developer dengan 5 tahun pengalaman di Laravel, MySQL, Redis. Berpengalaman membawa project dari development hingga production. Familiar dengan REST API, microservices architecture.",
        "Senior PHP Developer",
        "Mencari PHP expert dengan minimal 5 tahun pengalaman. Tech stack: Laravel, MySQL, Redis, Apache/Nginx. Tanggung jawab: design architecture, code review, mentoring junior developer."
    );

    echo "\n✅ Analysis Completed!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Match Score: " . $result['score'] . "/100\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\nAnalysis Details:\n";
    echo $result['analysis'] . "\n";
    
    echo "\n\n✨ Gemini API integration is working! ✨\n";

} catch (\Exception $e) {
    echo "❌ Error:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
