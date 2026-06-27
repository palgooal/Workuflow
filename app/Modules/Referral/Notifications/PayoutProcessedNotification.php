<?php

namespace App\Modules\Referral\Notifications;

use App\Modules\Referral\Models\ReferralPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PayoutProcessedNotification — تُرسَل للمسوّق عند معالجة طلب الصرف (paid أو rejected)
 *
 * Notifiable: $payout->affiliate->user (User model)
 * Channels: mail + database
 * Trigger: ReferralPayoutResource عند mark_paid أو reject
 */
class PayoutProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ReferralPayout $payout,
        public readonly bool           $approved, // true = paid, false = rejected
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount     = number_format($this->payout->amount, 2);
        $methodLabel = match ($this->payout->method?->value ?? $this->payout->method) {
            'bank'     => 'تحويل بنكي',
            'whatsapp' => 'واتساب باي',
            'credit'   => 'رصيد اشتراك',
            default    => $this->payout->method,
        };

        if ($this->approved) {
            return (new MailMessage)
                ->subject("✅ تم صرف \${$amount} — " . config('app.name'))
                ->greeting('مرحباً ' . ($notifiable->name ?? '') . ' ✅')
                ->line("تم معالجة طلب صرف مبلغ **\${$amount}** بنجاح.")
                ->line("طريقة الصرف: **{$methodLabel}**")
                ->line('قد يستغرق وصول المبلغ 1–3 أيام عمل حسب طريقة الصرف.')
                ->action('عرض سجل الصرف', route('affiliates.payouts'))
                ->line('شكراً لكونك جزءاً من برنامج الإحالات في ' . config('app.name') . '.');
        }

        return (new MailMessage)
            ->subject("❌ تعذّر معالجة طلب الصرف — " . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? '') . '')
            ->line("تأسفنا لإعلامك بأنه تعذّر معالجة طلب صرف **\${$amount}**.")
            ->line('سيعود الرصيد إلى حسابك خلال 24 ساعة.')
            ->line('يمكنك تقديم طلب صرف جديد في أي وقت.')
            ->action('طلب صرف جديد', route('affiliates.payouts'))
            ->line('للاستفسار، تواصل مع الدعم الفني.');
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->approved ? 'paid' : 'rejected';

        return [
            'type'       => 'payout_processed',
            'title'      => $this->approved
                ? '✅ تم صرف العمولة'
                : '❌ تعذّر صرف العمولة',
            'message'    => $this->approved
                ? "تم صرف \${$this->payout->amount} عبر " . ($this->payout->method?->value ?? $this->payout->method) . ' بنجاح.'
                : "تعذّر معالجة طلب صرف \${$this->payout->amount}. يمكنك إعادة المحاولة.",
            'payout_id'  => $this->payout->id,
            'amount'     => $this->payout->amount,
            'method'     => $this->payout->method?->value ?? $this->payout->method,
            'status'     => $status,
            'link'       => route('affiliates.payouts'),
            'icon'       => $this->approved ? '✅' : '❌',
        ];
    }
}
