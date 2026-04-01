<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileView extends Model
{
    protected $fillable = [
        'user_id',
        'viewed_by',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
