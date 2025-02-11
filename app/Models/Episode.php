<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = ['season_id', 'episode_number', 'title', 'description', 'trailer_url', 'episode_url', 'image_url'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
