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
            $table->unsignedTinyInteger('ai_match_score')->nullable()->after('admin_notes')->comment('AI match score 0-100');
            $table->enum('ai_analysis_status', ['pending', 'analyzing', 'completed', 'failed'])->default('pending')->after('ai_match_score')->comment('Status of AI analysis');
            $table->text('ai_analysis_details')->nullable()->after('ai_analysis_status')->comment('Detailed analysis from AI');
            $table->timestamp('ai_analyzed_at')->nullable()->after('ai_analysis_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['ai_match_score', 'ai_analysis_status', 'ai_analysis_details', 'ai_analyzed_at']);
        });
    }
};
