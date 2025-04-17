<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'name',
        'intro',
        'time_open',
        'time_close',
        'time_limit',
        'grade',
        'questions_count',
        'exam_type',
        'shuffle_questions',
        'shuffle_answers',
        'attempts',
        'feedback_enabled',
        'version',
        'question_pool',
        'status',
        // 'time_zone',
        'created_by',
    ];

    protected $casts = [
        'time_open'         => 'datetime',
        'time_close'        => 'datetime',
        'question_pool'     => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
                    ->withPivot('position', 'is_required')
                    ->withTimestamps();
    }
}
