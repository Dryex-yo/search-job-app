<?php

namespace App\Models;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Application extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'job_id',
        'user_id',
        'admin_id',
        'resume_path',
        'cover_letter',
        'status',
        'notes',
        'reviewed_at',
        'admin_notes',
        'ai_match_score',
        'ai_analysis_status',
        'ai_analysis_details',
        'ai_analyzed_at'
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ApplicationSubmitted::class,
    ];

    protected static function booted(): void
    {
        /**
         * Handle status change event
         */
        static::updating(function (Application $application) {
            if ($application->isDirty('status')) {
                $originalStatus = $application->getOriginal('status');
                static::updated(function (Application $model) use ($originalStatus) {
                    ApplicationStatusChanged::dispatch($model, $originalStatus);
                });
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'admin_notes', 'ai_match_score', 'reviewed_at'])
            ->logExcept(['created_at'])
            ->useLogName('application')
            ->setDescriptionForEvent(fn(string $eventName) => "Application has been {$eventName}");
    }

    // Relasi ke User (Pelamar)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Job (Pekerjaan yang dilamar)
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    // Relasi ke Admin yang melakukan review
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}