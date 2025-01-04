<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role; // Import the Role model
use App\Models\UserActivityLog; // Import UserActivityLog
use App\Models\Session; // Import Session (if it exists)

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role_id', // Updated to store role_id
        'first_name',
        'last_name',
        'phone_number',
        'sex',
        'address',
        'city',
        'zip_code',
        'profile_pic_url',
        'suspended',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended' => 'boolean',
        ];
    }

    /**
     * Relationship: User belongs to a Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship: User has many Activity Logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Relationship: User has many Sessions.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Relationship: User has many Roles (for pivot table role_user).
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps(); // Include pivot table timestamps
    }
}
