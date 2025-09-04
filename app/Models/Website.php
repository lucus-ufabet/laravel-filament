<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'default_language',
        'supported_languages',
        'logo_path',
        'favicon_path',
        'robots_txt',
        'google_tag',
        'site_manifest',
        'is_active',
    ];

    protected $casts = [
        'site_manifest' => 'array',
        'supported_languages' => 'array',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
