<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'type',
        'content_url',
        'description',
        'article_content',
        'module_data',
        'position',
        'visible',
        'release_date',
        'is_mandatory',
        'estimated_duration_minutes',
        'view_count',
        'prerequisite_modules',
        'rating',
        // 'slug',
    ];

    protected $casts = [
        'module_data' => 'array',
        'prerequisite_modules' => 'array',
        'visible' => 'boolean',
        'is_mandatory' => 'boolean',
        'release_date' => 'datetime',
        'rating' => 'decimal:2',
    ];

    // Define relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
