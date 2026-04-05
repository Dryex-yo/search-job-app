<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

/**
 * @mixin HasRoles
 * @mixin HasPermissions
 * @property string|null $role
 * @method bool hasRole($roles, ?string $guard = null)
 * @method bool can(string $permission, ?string $guard = null)
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPermissions, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'bio',
        'resume_path',
        'profile_photo_path',
        'address',
        'city',
        'province',
        'postal_code',
        'date_of_birth',
        'gender',
        'education',
        'education_level',
        'education_institution',
        'education_year_graduated',
        'education_major',
        'education_grade',
        'experience',
        'experiences',
        'skills',
        'id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'tenant_id',
        'tenant_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'experiences' => 'array',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }

    /**
     * Check if user is recruiter
     */
    public function isRecruiter(): bool
    {
        return $this->hasRole('recruiter') || $this->role === 'recruiter';
    }

    /**
     * Check if user is regular user (job seeker)
     */
    public function isUser(): bool
    {
        return $this->hasRole('user') || $this->role === 'user';
    }

    /**
     * Calculate profile completion percentage
     * Returns 0-100 based on how many profile fields are filled
     */
    public function getProfileCompletionPercentage(): int
    {
        // Define all profile fields that should be filled for 100% completion
        $profileFields = [
            // Basic required (must have name and email for 0%)
            'name' => 10,           // 4.76% weight
            'email' => 10,          // 4.76% weight
            
            // Contact info
            'phone' => 5,           // 2.38%
            'address' => 5,         // 2.38%
            'city' => 5,            // 2.38%
            'province' => 5,        // 2.38%
            'postal_code' => 5,     // 2.38%
            
            // Personal info
            'date_of_birth' => 5,   // 2.38%
            'gender' => 5,          // 2.38%
            'id_number' => 5,       // 2.38%
            'bio' => 5,             // 2.38%
            
            // Education
            'education_level' => 5,           // 2.38%
            'education_institution' => 5,    // 2.38%
            'education_major' => 5,          // 2.38%
            'education_year_graduated' => 5, // 2.38%
            'education_grade' => 5,          // 2.38%
            
            // Professional
            'experiences' => 10,    // 4.76%
            'skills' => 10,         // 4.76%
            
            // Files & Media
            'resume_path' => 5,              // 2.38%
            'profile_photo_path' => 5,       // 2.38%
            
            // Emergency
            'emergency_contact_name' => 5,   // 2.38%
            'emergency_contact_phone' => 5,  // 2.38%
        ];

        // Check if required fields (name, email) are filled
        if (empty($this->name) || empty($this->email)) {
            return 0; // Can't be above 0% without name and email
        }

        // Calculate total possible points
        $totalPoints = array_sum($profileFields);
        $earnedPoints = 0;

        // Check each field
        foreach ($profileFields as $field => $points) {
            $value = $this->{$field} ?? null;
            
            // Check if field is filled
            $isFilled = false;
            
            if ($field === 'experiences') {
                // experiences is array
                $isFilled = is_array($value) && count($value) > 0;
            } else {
                // All other fields are strings/scalar
                $isFilled = !empty($value);
            }
            
            if ($isFilled) {
                $earnedPoints += $points;
            }
        }

        // Calculate percentage
        $percentage = round(($earnedPoints / $totalPoints) * 100);
        
        return min(100, max(0, $percentage));
    }

    /**
     * Get profile views for this user
     */
    public function profileViews()
    {
        return $this->hasMany(ProfileView::class);
    }

    /**
     * Get applications for this user
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get the tenant this user belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get jobs posted by this recruiter
     */
    public function postedJobs()
    {
        return $this->hasMany(Job::class, 'recruiter_id');
    }

    /**
     * Scope: Get users from current tenant
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
     * Scope: Get all recruiter users
     */
    public function scopeRecruiters(Builder $query)
    {
        return $query->where('role', 'recruiter');
    }

    /**
     * Check if user belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        return $this->tenant_id === tenancy()->tenant()?->id;
    }
}

