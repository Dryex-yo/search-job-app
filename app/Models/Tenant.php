<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'domain',
        'database',
        'owner_name',
        'owner_email',
        'owner_phone',
        'industry',
        'company_size',
        'address',
        'city',
        'country',
        'status',
        'trial_ends_at',
        'subscription_plan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'domain';
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Relationships: Get recruiters for this tenant
     */
    public function recruiters()
    {
        return $this->hasMany(User::class)->where('role', 'recruiter');
    }

    /**
     * Get jobs posted by this tenant
     */
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get applications for this tenant
     */
    public function applications()
    {
        return $this->hasManyThrough(Application::class, Job::class);
    }
}
