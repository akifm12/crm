<?php
// app/Models/ResourceDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ResourceDocument extends Model
{
    protected $fillable = [
        'title', 'description', 'sector', 'category',
        'file_path', 'file_size', 'is_published',
    ];

    protected $casts = [
        'file_size'    => 'integer',
        'is_published' => 'boolean',
    ];

    public function downloadUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function fileSizeHuman(): string
    {
        if (! $this->file_size) {
            return '';
        }

        $kb = $this->file_size / 1024;

        return $kb < 1024
            ? round($kb) . ' KB'
            : round($kb / 1024, 1) . ' MB';
    }
}
