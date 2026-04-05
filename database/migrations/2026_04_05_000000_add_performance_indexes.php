<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add performance indexes based on stress test recommendations
     */
    public function up(): void
    {
        // Applications table indexes
        Schema::table('applications', function (Blueprint $table) {
            // Check before adding to avoid duplicate key errors
            if (!$this->hasIndex('applications', 'idx_status')) {
                $table->index('status', 'idx_status');
            }
            if (!$this->hasIndex('applications', 'idx_created_at')) {
                $table->index('created_at', 'idx_created_at');
            }
            if (!$this->hasIndex('applications', 'idx_job_user')) {
                $table->index(['job_id', 'user_id'], 'idx_job_user');
            }
        });

        // Jobs table indexes
        Schema::table('jobs', function (Blueprint $table) {
            if (!$this->hasIndex('jobs', 'idx_title')) {
                $table->index('title', 'idx_title');
            }
            if (!$this->hasIndex('jobs', 'idx_status_idx')) {
                $table->index('status', 'idx_status_idx');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'idx_role')) {
                $table->index('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_status');
            $table->dropIndexIfExists('idx_created_at');
            $table->dropIndexIfExists('idx_job_user');
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_title');
            $table->dropIndexIfExists('idx_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_role');
        });
    }

    /**
     * Check if an index exists
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEXES FROM $table WHERE Key_name = '$indexName'");
        return count($indexes) > 0;
    }
};
