<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    protected $fillable = [
        'ban_log_id',
        'user_id',
        'message',
        'admin_notes',
        'status',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
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

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => '⏳ Pending',
            'approved' => '✅ Approved',
            'rejected' => '❌ Rejected'
        ];
        return $labels[$this->status] ?? $this->status;
    }
}