<?php
// app/Http/Resources/UserDeadlineResource.php

namespace App\Http\Resources;

use App\Models\UserDeadline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDeadlineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$badgeColor, $badgeText] = $this->badge();

        return [
            'id' => $this->id,
            'compliance_deadline_id' => $this->compliance_deadline_id,
            'type' => $this->type,
            'type_label' => UserDeadline::typeLabels()[$this->type] ?? ucfirst($this->type),
            'label' => $this->label,
            'display_label' => $this->displayLabel(),
            'due_date' => $this->due_date?->toDateString(),
            'notes' => $this->notes,
            'badge_color' => $badgeColor,
            'badge_text' => $badgeText,
        ];
    }
}
