<?php

namespace App\Notifications;

use App\Models\PaymentOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly PaymentOrder $order,
        public readonly string       $reason = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Email
    // ──────────────────────────────────────────────────────────────────────
    public function toMail(object $notifiable): MailMessage
    {
        $planLabel = match ($this->order->plan) {
            'pro'      => 'Pro ⚡',
            'business' => 'Business 🚀',
            default    => ucfirst($this->order->plan ?? ''),
        };

        $mail = (new MailMessage)
            ->subject('❌ فشلت عملية الدفع — ' . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line("للأسف، لم تتم عملية الدفع لاشتراك **{$planLabel}**.")
            ->line("المبلغ: **\${$this->order->amount} {$this->order->currency}**");

        if ($this->reason) {
            $mail->line("السبب: {$this->reason}");
        }

        $checkoutUrl = $this->order->metadata['checkout_url'] ?? null;

        if ($checkoutUrl) {
            $mail->action('إعادة محاولة الدفع', $checkoutUrl);
        } else {
            $mail->action('اختر خطتك', route('billing.upgrade'));
        }

        $mail->line('إذا استمرت المشكلة تواصل مع الدعم الفني.');

        return $mail;
    }

    // ──────────────────────────────────────────────────────────────────────
    // In-app (database)
    // ──────────────────────────────────────────────────────────────────────
    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'payment_failed',
            'title'    => 'فشلت عملية الدفع ❌',
            'message'  => 'لم تتم عملية دفع خطة ' . match ($this->order->plan) {
                'pro'      => 'Pro ⚡',
                'business' => 'Business 🚀',
                default    => $this->order->plan,
            } . '. يمكنك إعادة المحاولة من صفحة الترقية.',
            'reason'   => $this->reason,
            'amount'   => $this->order->amount,
            'currency' => $this->order->currency,
            'plan'     => $this->order->plan,
            'order_id' => $this->order->id,
            'link'     => route('billing.upgrade'),
            'icon'     => '❌',
        ];
    }
}
