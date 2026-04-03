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
            // Interview scheduling fields
            $table->dateTime('interview_scheduled_at')->nullable()->after('reviewed_at')->comment('Scheduled interview datetime');
            $table->integer('interview_duration_minutes')->nullable()->after('interview_scheduled_at')->default(60)->comment('Interview duration in minutes');
            $table->enum('interview_type', ['technical', 'hr', 'general', 'final'])->nullable()->after('interview_duration_minutes')->comment('Type of interview');
            $table->string('interview_meeting_link')->nullable()->after('interview_type')->comment('Meeting link (Zoom/Google Meet)');
            $table->enum('interview_meeting_provider', ['zoom', 'google_meet', 'other'])->nullable()->after('interview_meeting_link')->comment('Meeting provider');
            $table->string('interview_calendar_event_id')->nullable()->after('interview_meeting_provider')->comment('Google Calendar event ID');
            $table->text('interview_notes')->nullable()->after('interview_calendar_event_id')->comment('Interview notes and details');
            $table->dateTime('interview_cancelled_at')->nullable()->after('interview_notes')->comment('Interview cancellation timestamp');
            
            // Indexes for performance
            $table->index('interview_scheduled_at');
            $table->index('interview_meeting_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'interview_scheduled_at',
                'interview_duration_minutes',
                'interview_type',
                'interview_meeting_link',
                'interview_meeting_provider',
                'interview_calendar_event_id',
                'interview_notes',
                'interview_cancelled_at',
            ]);
        });
    }
};
