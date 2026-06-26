<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notification_class',
        'user_id',
        'recipient',
        'status',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تسجيل إرسال ناجح
     */
    public static function success(string $notificationClass, ?int $userId, string $recipient): self
    {
        return static::create([
            'notification_class' => $notificationClass,
            'user_id'            => $userId,
            'recipient'          => $recipient,
            'status'             => 'sent',
        ]);
    }

    /**
     * تسجيل فشل إرسال
     */
    public static function failure(string $notificationClass, ?int $userId, string $recipient, string $error): self
    {
        return static::create([
            'notification_class' => $notificationClass,
            'user_id'            => $userId,
            'recipient'          => $recipient,
            'status'             => 'failed',
            'error_message'      => $error,
        ]);
    }
}
