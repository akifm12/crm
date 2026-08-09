<?php
// app/Http/Resources/NewsItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'body' => $this->body,
            'source_name' => $this->source_name,
            'source_url' => $this->source_url,
            'category' => $this->category,
            'is_ai_generated' => $this->isAiGenerated(),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
