<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    // use SoftDeletes;

    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'question_id',
        'position',
        'is_required',
        'created_by',
    ];

    protected $casts = [
        'position'    => 'integer',
        'is_required' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
