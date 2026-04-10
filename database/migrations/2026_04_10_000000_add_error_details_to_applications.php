<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Add error details column after ai_analysis_details
            $table->text('ai_analysis_error_details')->nullable()->after('ai_analysis_details')->comment('Error details when analysis failed (timeout, API error, etc)');
            $table->unsignedSmallInteger('ai_analysis_attempt_count')->default(0)->after('ai_analysis_error_details')->comment('Number of analysis attempts');
            $table->timestamp('ai_analysis_last_attempted_at')->nullable()->after('ai_analysis_attempt_count')->comment('Last time analysis attempt was made');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['ai_analysis_error_details', 'ai_analysis_attempt_count', 'ai_analysis_last_attempted_at']);
        });
    }
};
