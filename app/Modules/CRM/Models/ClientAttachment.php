<?php

namespace App\Modules\CRM\Models;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientAttachment extends Model
{
    use HasUlids;

    protected $table = 'client_attachments';

    protected $fillable = [
        'client_id',
        'user_id',
        'filename',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    // ==================== Relations ====================

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ==================== Helpers ====================

    /** رابط تنزيل الملف */
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** حجم الملف بصيغة مقروءة */
    public function humanSize(): string
    {
        $bytes = $this->size_bytes;

        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 2) . ' MB';
        if ($bytes >= 1024)     return round($bytes / 1024, 2) . ' KB';

        return $bytes . ' B';
    }

    /** هل الملف صورة؟ */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /** هل الملف PDF؟ */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
