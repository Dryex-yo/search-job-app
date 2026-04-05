<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Support\Facades\DB;

class PerformanceTestSeeder extends Seeder
{
    /**
     * Performance testing script for Search & Filtering
     * Tests with 10,000 applications
     */
    public function run(): void
    {
        $this->testSearchPerformance();
        $this->testFilteringPerformance();
        $this->testComplexQueries();
    }

    /**
     * Test search by job title
     */
    private function testSearchPerformance(): void
    {
        echo "\n╔═══════════════════════════════════════════╗\n";
        echo "║   SEARCH PERFORMANCE TEST (10,000 Data)  ║\n";
        echo "╚═══════════════════════════════════════════╝\n\n";

        $searchTerms = [
            'Software',
            'Engineer',
            'Manager',
            'Developer',
            'Designer'
        ];

        foreach ($searchTerms as $term) {
            $startTime = microtime(true);
            $startDb = $this->getDbQueryCount();

            $results = Application::whereHas('job', function ($query) use ($term) {
                $query->where('title', 'like', "%{$term}%");
            })->get();

            $endDb = $this->getDbQueryCount();
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000; // Convert to ms

            echo "🔍 Search: '{$term}'\n";
            echo "   ├─ Results: " . $results->count() . "\n";
            echo "   ├─ Time: " . round($duration, 2) . "ms\n";
            echo "   └─ Queries: " . ($endDb - $startDb) . "\n\n";
        }
    }

    /**
     * Test filtering by status, location, etc
     */
    private function testFilteringPerformance(): void
    {
        echo "\n╔═══════════════════════════════════════════╗\n";
        echo "║  FILTERING PERFORMANCE TEST (10,000 Data) ║\n";
        echo "╚═══════════════════════════════════════════╝\n\n";

        // Test 1: Filter by status
        echo "📊 Test 1: Filter by Application Status\n";
        $statuses = ['pending', 'shortlisted', 'accepted', 'rejected'];
        
        foreach ($statuses as $status) {
            $startTime = microtime(true);
            $startDb = $this->getDbQueryCount();

            $results = Application::where('status', $status)->get();

            $endDb = $this->getDbQueryCount();
            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            echo "   Status: '{$status}'\n";
            echo "   ├─ Results: " . $results->count() . "\n";
            echo "   ├─ Time: " . round($duration, 2) . "ms\n";
            echo "   └─ Queries: " . ($endDb - $startDb) . "\n";
        }

        // Test 2: Filter by date range
        echo "\n📅 Test 2: Filter by Date Range (Last 30 days)\n";
        $startTime = microtime(true);
        $startDb = $this->getDbQueryCount();

        $results = Application::where('created_at', '>=', now()->subDays(30))->get();

        $endDb = $this->getDbQueryCount();
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "   ├─ Results: " . $results->count() . "\n";
        echo "   ├─ Time: " . round($duration, 2) . "ms\n";
        echo "   └─ Queries: " . ($endDb - $startDb) . "\n";

        // Test 3: Combine multiple filters
        echo "\n🔗 Test 3: Combined Filters (Status + Date)\n";
        $startTime = microtime(true);
        $startDb = $this->getDbQueryCount();

        $results = Application::where('status', 'shortlisted')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $endDb = $this->getDbQueryCount();
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "   ├─ Results: " . $results->count() . "\n";
        echo "   ├─ Time: " . round($duration, 2) . "ms\n";
        echo "   └─ Queries: " . ($endDb - $startDb) . "\n";
    }

    /**
     * Test complex queries with relationships
     */
    private function testComplexQueries(): void
    {
        echo "\n╔═════════════════════════════════════════════╗\n";
        echo "║  COMPLEX QUERY PERFORMANCE TEST (10K Data)  ║\n";
        echo "╚═════════════════════════════════════════════╝\n\n";

        // Test 1: Query with eager loading
        echo "⚡ Test 1: Eager Loading (with relationships)\n";
        $startTime = microtime(true);
        $startDb = $this->getDbQueryCount();

        $results = Application::with(['user', 'job'])->get();

        $endDb = $this->getDbQueryCount();
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "   ├─ Results: " . $results->count() . "\n";
        echo "   ├─ Time: " . round($duration, 2) . "ms\n";
        echo "   └─ Queries: " . ($endDb - $startDb) . "\n";

        // Test 2: Query with pagination
        echo "\n📄 Test 2: Pagination (First 20 results)\n";
        $startTime = microtime(true);
        $startDb = $this->getDbQueryCount();

        $results = Application::with(['user', 'job'])->paginate(20);

        $endDb = $this->getDbQueryCount();
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "   ├─ Total: " . $results->total() . "\n";
        echo "   ├─ Per page: " . $results->count() . "\n";
        echo "   ├─ Time: " . round($duration, 2) . "ms\n";
        echo "   └─ Queries: " . ($endDb - $startDb) . "\n";

        // Test 3: Aggregation query
        echo "\n📈 Test 3: Aggregation Query\n";
        $startTime = microtime(true);
        $startDb = $this->getDbQueryCount();

        $stats = [
            'total' => Application::count(),
            'pending' => Application::where('status', 'pending')->count(),
            'shortlisted' => Application::where('status', 'shortlisted')->count(),
            'accepted' => Application::where('status', 'accepted')->count(),
            'rejected' => Application::where('status', 'rejected')->count(),
        ];

        $endDb = $this->getDbQueryCount();
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        echo "   ├─ Total: " . $stats['total'] . "\n";
        echo "   ├─ Pending: " . $stats['pending'] . "\n";
        echo "   ├─ Shortlisted: " . $stats['shortlisted'] . "\n";
        echo "   ├─ Accepted: " . $stats['accepted'] . "\n";
        echo "   ├─ Rejected: " . $stats['rejected'] . "\n";
        echo "   ├─ Time: " . round($duration, 2) . "ms\n";
        echo "   └─ Queries: " . ($endDb - $startDb) . "\n";
    }

    /**
     * Get database query count (simple estimate)
     */
    private function getDbQueryCount(): int
    {
        return DB::getQueryLog() ? count(DB::getQueryLog()) : 0;
    }
}
