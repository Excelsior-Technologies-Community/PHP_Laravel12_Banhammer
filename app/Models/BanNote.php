<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanNote extends Model
{
    protected $fillable = [
        'ban_log_id',
        'user_id',
        'note',
        'is_internal'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function banLog()
    {
        return $this->belongsTo(BanLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}