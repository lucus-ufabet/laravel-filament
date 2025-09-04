<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'name',
        'slug',
        'language',
        'type',
        'order',
        'is_published',
        'components',
        'tags',
        'audiences',
        'middlewares',
        'user_selectable',
    ];

    protected $casts = [
        'components' => 'array',
        'tags' => 'array',
        'audiences' => 'array',
        'middlewares' => 'array',
        'is_published' => 'boolean',
        'user_selectable' => 'boolean',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
