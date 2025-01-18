<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_category_id',
    ];

    /**
     * Relationships
     */

    // Parent Category (if applicable)
    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    // Subcategories
    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }
}
