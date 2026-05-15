<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'ip',
        'reason',
        'expired_at',
        'status',
        'type',
        'duration',
        'unbanned_at',
        'banned_by',
        'ip_address'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'unbanned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function banner()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'banned')
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'banned')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now());
    }

    public function isExpired()
    {
        if ($this->status !== 'banned') {
            return true;
        }

        if ($this->expired_at === null) {
            return false; // Permanent ban
        }

        return $this->expired_at <= now();
    }

    public function getDurationTextAttribute()
    {
        if ($this->duration == 'permanent') {
            return 'Permanent';
        }

        if ($this->duration) {
            return $this->duration . ' days';
        }

        return 'Unknown';
    }
}