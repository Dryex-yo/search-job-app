<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_name',
        'support_email',
        'max_file_upload_mb',
        'email_new_applications',
        'email_job_expiry',
        'email_weekly_reports',
        'email_user_feedback',
        'two_factor_enabled',
        'hiring_fee_per_person',
    ];

    protected $casts = [
        'email_new_applications' => 'boolean',
        'email_job_expiry' => 'boolean',
        'email_weekly_reports' => 'boolean',
        'email_user_feedback' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'max_file_upload_mb' => 'integer',
        'hiring_fee_per_person' => 'float',
    ];

    /**
     * Get the singleton instance of settings
     */
    public static function getInstance()
    {
        return static::firstOrCreate(
            [], // Always create with default values if not exists
            [
                'platform_name' => 'DRYEX',
                'support_email' => 'support@dryex.com',
                'max_file_upload_mb' => 10,
                'email_new_applications' => true,
                'email_job_expiry' => true,
                'email_weekly_reports' => true,
                'email_user_feedback' => false,
                'two_factor_enabled' => false,
                'hiring_fee_per_person' => 500.00,
            ]
        );
    }

    /**
     * Get a setting value
     */
    public static function get($key, $default = null)
    {
        $settings = static::getInstance();
        return $settings->$key ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value)
    {
        $settings = static::getInstance();
        $settings->$key = $value;
        $settings->save();
        return $settings;
    }
}
