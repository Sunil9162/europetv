<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'director',
        'producer',
        'release_year',
        'rating',
        'poster',
        'trailer_url',
        'movie_url'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'movie_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'movie_tag');
    }
}
