<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyPlans extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'name',
        'price',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];
}
