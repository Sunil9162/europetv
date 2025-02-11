<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'poster', 'status'];

    public function seasons()
    {
        return $this->hasMany(Season::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'series_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'series_tag');
    }
}
