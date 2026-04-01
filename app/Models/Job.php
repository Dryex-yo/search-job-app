<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'title',
        'company_name',
        'location',
        'salary',
        'description',
        'type',
        'status',
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
}

