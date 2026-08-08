<?php
// app/Models/NewsItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    protected $fillable = [
        'title', 'summary', 'body', 'source_name', 'source_url',
        'category', 'origin', 'published_at', 'is_published',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (NewsItem $item) {
            $item->source_url_hash = $item->source_url ? sha1($item->source_url) : null;
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function categoryBadgeColor(): string
    {
        return match ($this->category) {
            'aml'        => 'bg-red-100 text-red-700',
            'sanctions'  => 'bg-orange-100 text-orange-700',
            'regulatory' => 'bg-blue-100 text-blue-700',
            'industry'   => 'bg-emerald-100 text-emerald-700',
            'insight'    => 'bg-purple-100 text-purple-700',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    public function isAiGenerated(): bool
    {
        return $this->origin === 'ai_digest';
    }
}
