<?php

namespace App\Modules\Referral\Notifications;

use App\Modules\Referral\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * AffiliateApprovedNotification — تُرسَل للمسوّق عند اعتماد حسابه من الأدمن
 *
 * Notifiable: $affiliate->user (User model)
 * Channels: mail + database
 */
class AffiliateApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Affiliate $affiliate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dashboardUrl = route('affiliates.dashboard');
        $rate         = number_format($this->affiliate->commission_rate, 0);

        return (new MailMessage)
            ->subject('🎉 تم اعتماد حسابك كمسوّق في ' . config('app.name'))
            ->greeting('مرحباً ' . ($notifiable->name ?? '') . ' 🎉')
            ->line('يسعدنا إعلامك بأن طلبك للانضمام إلى برنامج الإحالات قد تمت الموافقة عليه!')
            ->line("ستحصل على عمولة **{$rate}٪** على كل اشتراك تُحيله.")
            ->line("كود الإحالة الخاص بك: **{$this->affiliate->display_code}**")
            ->action('ابدأ الآن — لوحة تحكم المسوّق', $dashboardUrl)
            ->line('شارك رابطك مع أصحابك وابدأ في كسب العمولات فوراً.')
            ->line('شكراً لاختيارك ' . config('app.name') . ' شريكاً لك.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'affiliate_approved',
            'title'         => '🎉 تم اعتماد حساب المسوّق',
            'message'       => "تهانينا! حسابك كمسوّق نشط الآن. عمولتك {$this->affiliate->commission_rate}٪ على كل اشتراك.",
            'affiliate_id'  => $this->affiliate->id,
            'display_code'  => $this->affiliate->display_code,
            'rate'          => $this->affiliate->commission_rate,
            'link'          => route('affiliates.dashboard'),
            'icon'          => '🎉',
        ];
    }
}
