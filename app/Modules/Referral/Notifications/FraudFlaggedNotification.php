<?php

namespace App\Modules\Referral\Notifications;

use App\Modules\Referral\Models\Affiliate;
use App\Modules\Referral\Models\ReferralCommission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * FraudFlaggedNotification — تُرسَل لمسؤول النظام عند رصد نشاط مشبوه
 *
 * Notifiable: OnDemand notification → ADMIN_EMAIL في config
 * Channels: mail فقط (لا database — هذا إشعار داخلي للأدمن)
 * Trigger: CreateReferralCommission Listener عند fraud_flagged = true
 *
 * استخدام:
 *   Notification::route('mail', config('referral.admin_email'))
 *       ->notify(new FraudFlaggedNotification($affiliate, $commission, $reasons));
 */
class FraudFlaggedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Affiliate          $affiliate,
        public readonly ReferralCommission $commission,
        public readonly array              $reasons = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminUrl   = url('/admin/referral-commissions/' . $this->commission->id);
        $reasonList = ! empty($this->reasons)
            ? implode("\n- ", $this->reasons)
            : 'لم تُحدَّد الأسباب';

        return (new MailMessage)
            ->subject('🚨 تنبيه احتيال — عمولة إحالة مشتبه بها')
            ->greeting('تنبيه أمني 🚨')
            ->line('رُصد نشاط مشبوه في نظام الإحالات يستوجب المراجعة الفورية.')
            ->line("**المسوّق:** {$this->affiliate->name} ({$this->affiliate->email})")
            ->line("**معرّف المسوّق:** `{$this->affiliate->id}`")
            ->line("**مبلغ العمولة:** \${$this->commission->amount}")
            ->line("**معرّف العمولة:** `{$this->commission->id}`")
            ->line("**أسباب الاشتباه:**\n- {$reasonList}")
            ->action('مراجعة العمولة في الأدمن', $adminUrl)
            ->line('العمولة مُعلَّقة تلقائياً (`fraud_flagged = true`) — يجب الاعتماد أو الرفض يدوياً.');
    }
}
