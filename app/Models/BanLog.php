<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'ip',
        'reason',
        'expired_at',
        'status'
    ];
}