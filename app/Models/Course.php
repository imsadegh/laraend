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
        'tuition_fee',
        'capacity',
        'visibility',
        'featured',
        'description',
        'about',
        'discussion_group_url',
        'skill_level',
        'is_free',
        'total_lectures',
        'lecture_length',
        'total_quizzes',
        'total_assignments',
        'total_resources',
        'language',
        'is_captions',
        'is_certificate',
        'is_quiz',
        'is_assignment',
        'table_of_content',

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

    // add a cast so that Laravel automatically decodes the JSON from the database into an array.
    protected $casts = [
        'prerequisites' => 'array',
        'tags' => 'array',
        'visibility' => 'boolean',
        // 'featured' => 'boolean',
        'table_of_content' => 'array',
        'allow_waitlist' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Relationships
     */

     public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }


    public function students()
    {
        return $this->belongsToMany(
            User::class,         // Related model
            'course_enrollments',// Pivot table name
            'course_id',         // Foreign key on pivot table for the course
            'user_id'            // Foreign key on pivot table for the student
        )->withTimestamps();
    }

    // This will ensure that if a single course name is provided (as a string), it’s wrapped into an array before being stored.
    public function setPrerequisitesAttribute($value)
    {
        $this->attributes['prerequisites'] = is_array($value)
            ? json_encode($value)
            : json_encode([$value]);
    }

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
