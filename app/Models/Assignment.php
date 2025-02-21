<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'submission_deadline', 'course_id', 'visible'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
