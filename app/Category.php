<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'slug'
    ];

    public function parent() {
        return $this->belongsTo(Category::class);
    }   

    public function child() {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeGetParent($query) {
        return $query->whereNull('parent_id');
    }

    // mutator
    public function setSlugAttribute($value) {
        $this->attributes['slug'] = Str::slug($value);
    }

    // accessor
    public function getNameAttribute($value) {
        return ucfirst($value);
    }
}
