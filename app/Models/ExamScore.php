<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamScore extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_id',
        'user_id',
        'score',
        'is_passed',
        'is_finalized',
        'is_graded_automatically',
        'final_grade',
        // 'submitted_at',
        'last_reviewed_at',
        'score_details',
        'grading_feedback',
        'is_reexam',
        'reviewed_by',
    ];

    protected $casts = [
        'score'                 => 'decimal:2',
        'final_grade'           => 'decimal:2',
        'is_passed'             => 'boolean',
        'is_finalized'          => 'boolean',
        'is_graded_automatically' => 'boolean',
        'is_reexam'             => 'boolean',
        'score_details'         => 'array',
        'grading_feedback'      => 'array',
        // 'submitted_at'          => 'datetime',
        'last_reviewed_at'      => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
