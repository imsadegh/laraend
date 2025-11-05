<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class CourseModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'created_by',
        'title',
        'type',
        'content_url',
        'description',
        'article_content',
        'module_data',
        'position',
        'visible',
        'release_date',
        'is_mandatory',
        'estimated_duration_minutes',
        'view_count',
        'prerequisite_modules',
        'rating',
        'encrypted_video_url',
        'video_title',
        'estimated_duration_seconds',
        'video_source',
        'video_added_at',
        'video_added_by',
        'video_metadata',
        // 'slug',
    ];

    protected $casts = [
        'module_data' => 'array',
        'prerequisite_modules' => 'array',
        'video_metadata' => 'array',
        'visible' => 'boolean',
        'is_mandatory' => 'boolean',
        'release_date' => 'datetime',
        'video_added_at' => 'datetime',
        'rating' => 'decimal:2',
    ];

    // Define relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function videoAddedBy()
    {
        return $this->belongsTo(User::class, 'video_added_by');
    }

    /**
     * Encrypt video URL when setting it
     */
    public function setEncryptedVideoUrlAttribute($value)
    {
        if ($value) {
            $this->attributes['encrypted_video_url'] = Crypt::encrypt($value);
        } else {
            $this->attributes['encrypted_video_url'] = null;
        }
    }

    /**
     * Decrypt video URL when retrieving it
     */
    public function getEncryptedVideoUrlAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (\Exception $e) {
                // If decryption fails, return null
                return null;
            }
        }
        return null;
    }
}
