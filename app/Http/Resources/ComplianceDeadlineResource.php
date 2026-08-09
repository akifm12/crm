<?php
// app/Http/Resources/ComplianceDeadlineResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceDeadlineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sector' => $this->sector,
            'category' => $this->category,
            'authority' => $this->authority,
            'recurrence' => $this->recurrence,
            'next_due_date' => $this->next_due_date?->toDateString(),
            'source_url' => $this->source_url,
            'my_deadline' => $this->relationLoaded('userDeadlines') && $this->userDeadlines->isNotEmpty()
                ? new UserDeadlineResource($this->userDeadlines->first())
                : null,
        ];
    }
}
