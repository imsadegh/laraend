<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TuitionHistory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * By default, Eloquent will assume the table name is the plural of the model name ("tuition_histories"),
     * so you can explicitly define it here if you've named the table 'tuition_history'.
     */
    protected $table = 'tuition_history';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'amount_paid',
        'installment_number',
        'payment_status',
        'payment_date',
        'payment_method',
        'transaction_id',
        'payment_details',
        'last_updated_status_at',
        'is_refundable',
        'refund_amount',
        'refund_status',
        'receipt_url',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'payment_details' => 'array',
        'is_refundable' => 'boolean',
        'amount_paid' => 'decimal:2',
        'installment_number' => 'integer',
        'refund_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'last_updated_status_at' => 'datetime',
    ];



    /**
     * Relationship: a tuition history record belongs to a user (student).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: a tuition history record belongs to a course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
