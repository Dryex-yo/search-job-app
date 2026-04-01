<?php

namespace App\Models;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'admin_id',
        'resume_path',
        'cover_letter',
        'status',
        'notes',
        'reviewed_at',
        'admin_notes'
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