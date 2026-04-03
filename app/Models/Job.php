<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Job extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'title',
        'company_name',
        'location',
        'salary',
        'description',
        'type',
        'status',
        'tenant_id',
        'recruiter_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get the tenant this job belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the recruiter who posted this job
     */
    public function recruiter()
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    /**
     * Scope: Get jobs from current tenant
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
     * Scope: Get active jobs only
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if job belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        return $this->tenant_id === tenancy()->tenant()?->id;
    }
}

