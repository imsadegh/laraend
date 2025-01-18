<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Course extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'course_name',
        'course_code',
        'teacher_id',
        'assistant_id',
        'instructor_id',
        'category_id',
        'capacity',
        'visibility',
        'featured',
        'description',
        'about',
        'discussion_group_url',
        'status',
        'allow_waitlist',
        'start_date',
        'end_date',
        'prerequisites',
        'tags',
        'thumbnail_url',
        'enrolled_students_count',
    ];

    protected $casts = [
        'prerequisites' => 'array',
        'tags' => 'array',
        'visibility' => 'boolean',
        'featured' => 'boolean',
        'allow_waitlist' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Relationships
     */

    // Teacher (mandatory)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Assistant (optional)
    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    // Instructor (optional)
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Category (mandatory)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
