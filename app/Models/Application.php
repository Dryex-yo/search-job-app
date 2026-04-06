<?php

namespace App\Models;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Application extends Model
{
    use HasFactory, LogsActivity, UsesTenantConnection;

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
        'ai_analyzed_at',
        'interview_scheduled_at',
        'interview_duration_minutes',
        'interview_type',
        'interview_meeting_link',
        'interview_meeting_provider',
        'interview_calendar_event_id',
        'interview_notes',
        'interview_cancelled_at',
        'tenant_id',
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
         * Ensure tenant_id is always set when creating an application
         */
        static::creating(function (Application $application) {
            if (!$application->tenant_id) {
                try {
                    $tenantManager = app('tenancy');
                    $currentTenant = $tenantManager->tenant();
                    if ($currentTenant) {
                        $application->tenant_id = $currentTenant->id;
                    }
                } catch (\Exception $e) {
                    // Tenant context not available, let UsesTenantConnection handle it
                }
            }
        });

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

    /**
     * Get the tenant this application belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope: Get applications from current tenant
     */
    public function scopeForTenant(Builder $query, $tenantId = null)
    {
        $tenantId = $tenantId ?? tenancy()->tenant()?->id;
        
        if ($tenantId) {
            return $query->where('tenant_id', $tenantId);
        }
        
        return $query;
    }

    /**
     * Scope: Get pending applications
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if application belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        return $this->tenant_id === tenancy()->tenant()?->id;
    }
}