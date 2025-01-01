<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_details',
        'ip_address',
        'user_agent',
        'activity_time',
        'session_id',
        'activity_metadata',
        'duration_seconds',
        'is_successful',
        'referrer_url',
        'activity_category',
    ];
}
