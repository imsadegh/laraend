<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamAttempt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exam_id',
        'user_id',
        'attempt_number',
        'started_at',
        'finished_at',
        'is_submitted',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_submitted' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function examScore()
    {
        return $this->hasOne(ExamScore::class, 'exam_id', 'exam_id')
            // ->where('user_id', $this->user_id);

            // ->whereColumn('exam_scores.user_id', 'exam_attempts.user_id');
            ->where('is_reexam', false);           // ignore re-exams if you like
    }

}
