<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    protected $fillable = [
        'user_id',
        'issued_by',
        'reason',
        'level',
        'status',
        'expires_at',
        'read_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isExpired()
    {
        if ($this->expires_at) {
            return $this->expires_at <= now();
        }
        return false;
    }

    public function getLevelLabelAttribute()
    {
        $labels = [
            1 => '⚠️ Level 1 - Warning',
            2 => '⚠️ Level 2 - Serious Warning',
            3 => '🚫 Level 3 - Final Warning'
        ];
        return $labels[$this->level] ?? 'Warning';
    }
}