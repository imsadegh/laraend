<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_name',
        'course_code',
        'instructor_id',
        'assistant_id',
        'category_id',
        'capacity',
        'visibility',
        'featured',
        'description',
        'about',
        'discussion_group_url',
        'status',
        'is_finished',
        // 'enrolled_students_count',
        'allow_waitlist',
        'start_date',
        'end_date',
        'prerequisites',
        'tags',
        'thumbnail_url',
        'rating',
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'tags' => 'array',
        'visibility' => 'boolean',
        // 'featured' => 'boolean',
        'allow_waitlist' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Instructor (mandatory)
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Assistant (optional)
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    // Category (mandatory)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
