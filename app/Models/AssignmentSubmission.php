<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'submission_date',
        'file_path',
        'comments',
        'score',
        'revision_number',
        'is_late',
        'feedback',
        'last_reviewed_at',
        'reviewed_by',
        'grade_visibility',
        'metadata',
    ];

    protected $casts = [
        // 'feedback' => 'array',
        'metadata' => 'array',
    ];

    // Relationship to the Assignment
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Relationship to the User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }


}
