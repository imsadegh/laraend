<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role; // Import the Role model
use App\Models\UserActivityLog; // Import UserActivityLog
use App\Models\Session; // Import Session (if it exists)

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'full_name',
        'phone_number',
        'melli_code',
        'birth_date',
        'position_title',
        'sex',
        'address',
        'city',
        'zip_code',
        'avatar',
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

    public function getJWTIdentifier()
    {
        // Return the primary key of the user
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Return any custom claims you want to add to the JWT
        return [];
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
     * Relationship: User belongs to a Role.
     */
    public function role()
    {
        // return $this->belongsTo(Role::class);
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relationship: User has many Roles (for pivot table role_user).
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps(); // Include pivot table timestamps
    }

    /**
     * Get the ability rules for the user based on their role.
     *
     * @return array
     */
    public function getAbilityRules(): array
    {
        switch ($this->role_id) {
            // todo - need to update roles and permissions
            case 1: // Student
                return [
                    ['action' => 'read', 'subject' => 'AclDemo'], // this is a temporary role
                    ['action' => 'read', 'subject' => 'Course'],
                    ['action' => 'submit', 'subject' => 'Assignment'],
                ];
                case 2: // Instructor
                    return [
                    // ['action' => 'manage', 'subject' => 'all'],
                    ['action' => 'manage', 'subject' => 'AclDemo'], // this is a temporary role
                    ['action' => 'manage', 'subject' => 'Course'],
                    ['action' => 'grade', 'subject' => 'Assignment'],
                ];
            case 3: // Assistant
                return [
                    ['action' => 'assist', 'subject' => 'Course'],
                    ['action' => 'submit', 'subject' => 'Grade'],
                ];
            case 5: // Admin
                return [
                    ['action' => 'manage', 'subject' => 'all'],
                ];
            default:
                return [];
        }
    }

}
