<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Add event column if it doesn't exist
            if (!Schema::hasColumn('activity_log', 'event')) {
                $table->string('event')->nullable()->after('subject_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            if (Schema::hasColumn('activity_log', 'event')) {
                $table->dropColumn('event');
            }
        });
    }
};
