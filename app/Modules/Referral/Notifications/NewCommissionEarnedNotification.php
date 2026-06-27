<?php

namespace App\Modules\Referral\Notifications;

use App\Modules\Referral\Models\ReferralCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * NewCommissionEarnedNotification — تُرسَل للمسوّق عند إنشاء عمولة جديدة (pending)
 *
 * Notifiable: $commission->affiliate->user (User model)
 * Channels: mail + database
 * Trigger: CreateReferralCommissionAction بعد إنشاء العمولة
 */
class NewCommissionEarnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ReferralCommission $commission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planLabel  = match ($this->commission->subscription_plan) {
            'pro'      => 'Pro ⚡',
            'business' => 'Business 🚀',
            default    => ucfirst($this->commission->subscription_plan ?? ''),
        };
        $cycleLabel = match ($this->commission->subscription_cycle) {
            'annual'  => 'سنوي',
            default   => 'شهري',
        };
        $amount = number_format($this->commission->amount, 2);

        return (new MailMessage)
            ->subject("💰 عمولة جديدة \${$amount} — " . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? '') . ' 💰')
            ->line("تم تسجيل عمولة جديدة بقيمة **\${$amount}** في حسابك!")
            ->line("أحد المُحالين اشترك في خطة **{$planLabel}** ({$cycleLabel}).")
            ->line('العمولة في حالة **معلّقة** — ستُعتمد خلال 7 أيام عمل.')
            ->action('عرض عمولاتك', route('affiliates.commissions'))
            ->line('استمر في المشاركة لكسب المزيد!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'new_commission_earned',
            'title'             => '💰 عمولة جديدة',
            'message'           => "كسبت \${$this->commission->amount} من اشتراك " .
                                   ucfirst($this->commission->subscription_plan ?? '') . ' — قيد المراجعة.',
            'commission_id'     => $this->commission->id,
            'amount'            => $this->commission->amount,
            'plan'              => $this->commission->subscription_plan,
            'cycle'             => $this->commission->subscription_cycle,
            'fraud_flagged'     => $this->commission->fraud_flagged,
            'link'              => route('affiliates.commissions'),
            'icon'              => '💰',
        ];
    }
}
