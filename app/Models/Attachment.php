<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
        'user_id',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIconAttribute(): string
    {
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => 'file-text',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'xls', 'xlsx', 'csv' => 'table',
            'doc', 'docx' => 'file-text',
            default => 'file',
        };
    }
}
