<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'title',
        'question_text',
        'type',
        'options',
        'correct_answers',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
    ];
}
