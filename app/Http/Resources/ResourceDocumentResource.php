<?php
// app/Http/Resources/ResourceDocumentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sector' => $this->sector,
            'category' => $this->category,
            'download_url' => $this->downloadUrl(),
            'file_size_human' => $this->fileSizeHuman(),
        ];
    }
}
