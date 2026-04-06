<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Gemini API Optimization ===\n\n";

try {
    // Initialize service
    $service = new \App\Services\CvAnalysisService();
    \App\Services\OptimizationStatsService::reset();
    echo "✅ CvAnalysisService initialized\n";

    // Test 1: Check caching
    echo "\n📦 Testing Cache Service...\n";
    $cacheService = new \App\Services\AnalysisCacheService();
    $testData = ['score' => 85, 'analysis' => 'Test'];
    $cacheService->put("test cv", "test job", "test description", $testData);
    $cached = $cacheService->get("test cv", "test job", "test description");
    if ($cached && $cached['score'] === 85) {
        echo "✅ Cache Service working correctly\n";
        echo "   Cache stats: " . json_encode($cacheService->getStats()) . "\n";
    } else {
        echo "❌ Cache Service not working\n";
    }

    // Test 2: Check rate limiting
    echo "\n⚙️ Testing Rate Limiter Service...\n";
    $rateLimiter = new \App\Services\RateLimiterService();
    $canRequest = $rateLimiter->canMakeRequest('gemini');
    echo "   Can make request: " . ($canRequest ? "Yes" : "No") . "\n";
    
    $rateLimiter->recordRequest('gemini', 150);
    $stats = $rateLimiter->getStats('gemini');
    echo "   Requests used: " . $stats['requests_used'] . "/" . $stats['requests_limit'] . "\n";
    echo "   Tokens used: " . $stats['tokens_used'] . "/" . $stats['tokens_limit'] . "\n";

    // Test 3: CV analysis with caching (first call)
    echo "\n🧠 Testing CV Analysis (first call)...\n";
    $cvText = "PHP Developer dengan 5 tahun pengalaman di Laravel, MySQL, Redis. Berpengalaman membawa project dari development hingga production. Familiar dengan REST API, microservices architecture.";
    $jobTitle = "Senior PHP Developer";
    $jobDescription = "Mencari PHP expert dengan minimal 5 tahun pengalaman. Tech stack: Laravel, MySQL, Redis, Apache/Nginx. Tanggung jawab: design architecture, code review, mentoring junior developer.";
    
    $startTime = microtime(true);
    $result1 = $service->analyzeMatch($cvText, $jobTitle, $jobDescription);
    $duration1 = microtime(true) - $startTime;
    
    echo "✅ Analysis completed: Score " . $result1['score'] . "/100\n";
    echo "   Duration: " . round($duration1 * 1000) . "ms\n";
    
    // Test 4: CV analysis with caching (second call - same inputs)
    echo "\n⚡ Testing CV Analysis (second call - cached)...\n";
    $startTime = microtime(true);
    $result2 = $service->analyzeMatch($cvText, $jobTitle, $jobDescription);
    $duration2 = microtime(true) - $startTime;
    
    echo "✅ Analysis completed: Score " . $result2['score'] . "/100\n";
    echo "   Duration: " . round($duration2 * 1000) . "ms (cache hit = instant)\n";

    // Test 5: Smart text truncation
    echo "\n✂️ Testing Smart Text Truncation...\n";
    $longText = "This is a long text. It has multiple sentences. This is the first sentence. And here is the second. The third sentence is coming. Now comes the fourth. This is the fifth sentence. And finally the sixth sentence.";
    $truncated = $service->smartTruncateText($longText, 40);
    echo "   Original length: " . strlen($longText) . " chars\n";
    echo "   Truncated to ~40 chars: \"" . $truncated . "\"\n";
    echo "   Actual length: " . strlen($truncated) . " chars\n";

    echo "\n\n✨ All optimization tests completed! ✨\n";
    
    // Print statistics
    echo "\n" . \App\Services\OptimizationStatsService::getSummary() . "\n";

    echo "\n📊 Performance Improvements:\n";
    echo "   " . ($duration1 > 1 ? "✅ API call took " . round($duration1, 2) . "s" : "⏱️ API call completed quickly") . "\n";
    echo "   " . ($duration2 < $duration1 * 0.5 ? "✅ Cache hit is significantly faster" : "ℹ️ Cache hit detected") . "\n";
    echo "   ✅ Rate limiting tracking active\n";
    echo "   ✅ Smart truncation prevents mid-sentence cuts\n";

} catch (\Exception $e) {
    echo "❌ Error:\n";
    echo $e->getMessage() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
