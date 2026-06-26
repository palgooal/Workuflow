<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedPaymentCallback extends Model
{
    protected $fillable = [
        'provider',
        'order_id',
        'payload',
        'exception',
        'retries',
        'resolved',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'resolved'     => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * سجِّل callback فاشل بسرعة من أي مكان في الكود.
     */
    public static function record(
        string     $provider,
        ?string    $orderId,
        array      $payload,
        \Throwable $exception,
    ): self {
        return static::create([
            'provider'  => $provider,
            'order_id'  => $orderId,
            'payload'   => $payload,
            'exception' => $exception->getMessage() . "\n" . $exception->getTraceAsString(),
        ]);
    }

    public function markResolved(): void
    {
        $this->update([
            'resolved'     => true,
            'processed_at' => now(),
        ]);
    }

    public function incrementRetry(): void
    {
        $this->increment('retries');
    }
}
