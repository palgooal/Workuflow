<?php

namespace App\Notifications;

use App\Models\PaymentOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly PaymentOrder $order,
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
        $planLabel  = match ($this->order->plan) {
            'pro'      => 'Pro ⚡',
            'business' => 'Business 🚀',
            default    => ucfirst($this->order->plan ?? ''),
        };
        $cycleLabel = match ($this->order->cycle) {
            'annual'  => 'سنوي (12 شهراً)',
            default   => 'شهري',
        };

        return (new MailMessage)
            ->subject('✅ تم الدفع بنجاح — ' . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? '') . ' 🎉')
            ->line("تم استلام دفعتك بنجاح وتفعيل اشتراك **{$planLabel}** ({$cycleLabel}).")
            ->line("المبلغ المدفوع: **\${$this->order->amount} {$this->order->currency}**")
            ->line("رقم الطلب: `{$this->order->id}`")
            ->action('الذهاب إلى لوحة التحكم', route('dashboard'))
            ->line('شكراً لاختيارك ' . config('app.name') . ' لإدارة أعمالك.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // In-app (database)
    // ──────────────────────────────────────────────────────────────────────
    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'payment_successful',
            'title'    => 'تم الدفع بنجاح ✅',
            'message'  => 'تم تفعيل اشتراك ' . match ($this->order->plan) {
                'pro'      => 'Pro ⚡',
                'business' => 'Business 🚀',
                default    => $this->order->plan,
            } . ' بنجاح. استمتع بجميع الميزات.',
            'amount'   => $this->order->amount,
            'currency' => $this->order->currency,
            'plan'     => $this->order->plan,
            'cycle'    => $this->order->cycle,
            'order_id' => $this->order->id,
            'link'     => route('billing.index'),
            'icon'     => '✅',
        ];
    }
}
