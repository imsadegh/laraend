<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;
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
