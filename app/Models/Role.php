<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relationship: Role has many Users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relationship: Role belongs to many Users (Pivot Table).
     */
    public function usersWithPivot()
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps(); // Include pivot table timestamps
    }
}
