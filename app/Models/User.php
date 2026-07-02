<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Mchev\Banhammer\Traits\Bannable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Bannable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function banLogs()
    {
        return $this->hasMany(BanLog::class);
    }

    public function issuedWarnings()
    {
        return $this->hasMany(Warning::class, 'issued_by');
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }

    public function banNotes()
    {
        return $this->hasMany(BanNote::class);
    }

    public function getIsBannedAttribute()
    {
        return $this->isBanned();
    }

    public function getBanInfoAttribute()
    {
        if ($this->isBanned()) {
            $ban = $this->bans()->whereNull('deleted_at')->latest()->first();
            return [
                'banned' => true,
                'reason' => $ban?->comment,
                'expired_at' => $ban?->expired_at,
                'is_permanent' => $ban?->expired_at === null,
            ];
        }
        return ['banned' => false];
    }

    public function bannedAt()
    {
        $ban = $this->bans()->whereNull('deleted_at')->latest()->first();
        return $ban?->created_at;
    }

    public function isCurrentlyBanned()
    {
        return $this->isBanned();
    }

    public function getBanReason()
    {
        $ban = $this->bans()->whereNull('deleted_at')->latest()->first();
        return $ban?->comment;
    }

    public function getBanExpiry()
    {
        $ban = $this->bans()->whereNull('deleted_at')->latest()->first();
        return $ban?->expired_at;
    }

    public function isBannedPermanently()
    {
        $ban = $this->bans()->whereNull('deleted_at')->latest()->first();
        return $ban && $ban->expired_at === null;
    }
}