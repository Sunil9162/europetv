<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration',
        'max_quality',
        'max_device',
        'resolution',
        'support',
        'trial_period',
        'status',
        'description'
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'float',
        'support' => 'boolean',
        'trial_period' => 'integer',
    ];
}
