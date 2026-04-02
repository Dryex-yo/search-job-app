<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    use HasFactory, Notifiable, HasRoles, HasPermissions;

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
}
