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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('DRYEX');
            $table->string('support_email')->default('support@dryex.com');
            $table->integer('max_file_upload_mb')->default(10);
            $table->boolean('email_new_applications')->default(true);
            $table->boolean('email_job_expiry')->default(true);
            $table->boolean('email_weekly_reports')->default(true);
            $table->boolean('email_user_feedback')->default(false);
            $table->boolean('two_factor_enabled')->default(false);
            $table->decimal('hiring_fee_per_person', 10, 2)->default(500.00)->comment('Fee charged per hire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
