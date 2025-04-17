<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'attempt_number',
        // 'score_total',
        'started_at',
        'finished_at',
        'is_submitted',
    ];

    protected $casts = [
        // 'score_total'    => 'decimal:2',
        'attempt_number' => 'integer',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
        'is_submitted'   => 'boolean',
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
}
