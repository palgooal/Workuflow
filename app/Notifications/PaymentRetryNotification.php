<?php

namespace App\Notifications;

use App\Models\PaymentOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * يُرسَل من Admin عند الضغط على "إعادة المحاولة" في PaymentOrderResource
 */
class PaymentRetryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly PaymentOrder $order,
        public readonly string       $checkoutUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planLabel = match ($this->order->plan) {
            'pro'      => 'Pro ⚡',
            'business' => 'Business 🚀',
            default    => ucfirst($this->order->plan ?? ''),
        };

        return (new MailMessage)
            ->subject('🔄 إعادة محاولة الدفع — ' . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line("تم تجهيز رابط دفع جديد لاشتراك **{$planLabel}**.")
            ->line("المبلغ: **\${$this->order->amount} {$this->order->currency}**")
            ->action('إتمام الدفع الآن', $this->checkoutUrl)
            ->line('الرابط صالح لجلسة دفع واحدة. إذا احتجت مساعدة تواصل مع الدعم.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'payment_retry',
            'title'    => '🔄 رابط دفع جديد بانتظارك',
            'message'  => 'تم تجهيز رابط دفع جديد لاشتراك ' . match ($this->order->plan) {
                'pro'      => 'Pro ⚡',
                'business' => 'Business 🚀',
                default    => $this->order->plan,
            } . '. أكمل الدفع الآن.',
            'order_id'    => $this->order->id,
            'checkout_url' => $this->checkoutUrl,
            'link'     => $this->checkoutUrl,
            'icon'     => '🔄',
        ];
    }
}
