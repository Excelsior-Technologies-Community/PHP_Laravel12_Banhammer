<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'ip_address',
        'warning_level',
        'auto_ban',
        'appeal_status'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'unbanned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'auto_ban' => 'boolean',
        'warning_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banner(): BelongsTo
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

    public function scopePendingAppeals($query)
    {
        return $query->where('appeal_status', 'pending');
    }

    public function scopeUserType($query)
    {
        return $query->where('type', 'user');
    }

    public function scopeIpType($query)
    {
        return $query->where('type', 'ip');
    }

    public function isExpired(): bool
    {
        if ($this->status !== 'banned') {
            return true;
        }

        if ($this->expired_at === null) {
            return false;
        }

        return $this->expired_at <= now();
    }

    public function isPermanent(): bool
    {
        return $this->duration === 'permanent' || $this->expired_at === null;
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration === 'permanent' || $this->expired_at === null) {
            return 'Permanent';
        }

        if ($this->duration) {
            return $this->duration . ' days';
        }

        return 'Unknown';
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'banned' => 'danger',
            'unbanned' => 'success',
            'expired' => 'warning'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getAppealStatusBadgeAttribute(): string
    {
        $badges = [
            'none' => 'secondary',
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        return $badges[$this->appeal_status] ?? 'secondary';
    }

    public function getAppealStatusTextAttribute(): string
    {
        $labels = [
            'none' => 'No Appeal',
            'pending' => '⏳ Pending Review',
            'approved' => '✅ Approved',
            'rejected' => '❌ Rejected'
        ];

        return $labels[$this->appeal_status] ?? $this->appeal_status;
    }

    public function getWarningLevelTextAttribute(): string
    {
        $levels = [
            0 => 'No Warning',
            1 => '⚠️ Level 1 - Warning',
            2 => '⚠️ Level 2 - Serious',
            3 => '🚫 Level 3 - Final'
        ];

        return $levels[$this->warning_level] ?? 'Unknown';
    }

    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'user' => 'primary',
            'ip' => 'secondary'
        ];

        return $badges[$this->type] ?? 'secondary';
    }

    public function getTypeIconAttribute(): string
    {
        $icons = [
            'user' => 'fas fa-user',
            'ip' => 'fas fa-network-wired'
        ];

        return $icons[$this->type] ?? 'fas fa-circle';
    }

    public function getRemainingDaysAttribute(): ?int
    {
        if ($this->status !== 'banned' || $this->expired_at === null) {
            return null;
        }

        $diff = now()->diffInDays($this->expired_at, false);
        return $diff > 0 ? $diff : 0;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'banned' && !$this->isExpired();
    }

    public function getCanAppealAttribute(): bool
    {
        return $this->status === 'banned' && 
               $this->type === 'user' && 
               $this->appeal_status === 'none';
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class, 'user_id', 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(BanNote::class);
    }

    public function appeal()
    {
        return $this->hasOne(Appeal::class);
    }
}