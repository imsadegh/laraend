<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'course_id', 'title', 'description', 'submission_deadline', 'requirements', 'max_score', 'is_active', 'type',
        'allow_late_submission', 'visible', 'late_submission_penalty', 'resources', 'revision_limit', 'published_at',
        'last_submission_at',
    ];

    /**
     * Relationship to the Course model.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
