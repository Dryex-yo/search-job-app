<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'resume_path',
        'cover_letter',
        'status'
    ];

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
}