<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'languages',
        'structure',
        'is_published',
    ];

    protected $casts = [
        'languages' => 'array',
        'structure' => 'array',
        'is_published' => 'boolean',
    ];
}

