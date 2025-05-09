<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseEnrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        // Optionally add other fields such as 'enrolled_at' if needed
        'status',
        'amount_paid'
    ];

    public function student()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function course()
{
    return $this->belongsTo(Course::class, 'course_id');
}


}
