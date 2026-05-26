<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'clearance_id',
        'file_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'size_bytes'  => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            Storage::disk('attachments')->delete($attachment->stored_path);
        });
    }

    public function clearance()
    {
        return $this->belongsTo(Clearance::class);
    }

    /** Human-readable file size, e.g. "1.4 MB", "320 KB". */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024)    return round($bytes / 1_024, 1) . ' KB';
        return $bytes . ' B';
    }

    /** True when the file is an image type that browsers can display inline. */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/jpg'], true);
    }
}
