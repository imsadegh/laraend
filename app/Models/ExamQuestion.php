<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'question_id',
        'position',
        'is_required',
    ];

    protected $casts = [
        'position'    => 'integer',
        'is_required' => 'boolean',
    ];

    /**
     * The exam this question belongs to.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * The question attached to this exam.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
