<?php

namespace App\Modules\Referral\Notifications;

use App\Modules\Referral\Enums\AffiliateTier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * TierUpgradedNotification — تُرسَل للمسوّق عند ترقية مستواه
 *
 * Notifiable: $affiliate->user (User model)
 * Channels: mail + database
 * Trigger: UpgradeAffiliateTierAction بعد كل عمولة جديدة
 */
class TierUpgradedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly AffiliateTier $newTier,
        public readonly AffiliateTier $oldTier,
        public readonly float         $newRate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tierLabel    = $this->tierLabel($this->newTier);
        $oldTierLabel = $this->tierLabel($this->oldTier);
        $rate         = number_format($this->newRate, 0);

        return (new MailMessage)
            ->subject("⭐ تمت ترقيتك إلى {$tierLabel} — " . config('app.name'))
            ->greeting('مبروك ' . ($notifiable->name ?? '') . ' ⭐')
            ->line("تمت ترقيتك من **{$oldTierLabel}** إلى **{$tierLabel}**!")
            ->line("نسبة عمولتك الجديدة: **{$rate}٪** على كل اشتراك تُحيله.")
            ->line($this->tierBenefitLine())
            ->action('لوحة تحكم المسوّق', route('affiliates.dashboard'))
            ->line('استمر في الإحالة للوصول إلى مستويات أعلى.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'tier_upgraded',
            'title'     => "⭐ ترقية مستوى — {$this->tierLabel($this->newTier)}",
            'message'   => "ترقيت من {$this->tierLabel($this->oldTier)} إلى {$this->tierLabel($this->newTier)}. عمولتك الجديدة {$this->newRate}٪.",
            'new_tier'  => $this->newTier->value,
            'old_tier'  => $this->oldTier->value,
            'new_rate'  => $this->newRate,
            'link'      => route('affiliates.dashboard'),
            'icon'      => '⭐',
        ];
    }

    private function tierLabel(AffiliateTier $tier): string
    {
        return match ($tier) {
            AffiliateTier::Standard => 'Standard 🌱',
            AffiliateTier::Silver   => 'Silver 🥈',
            AffiliateTier::Gold     => 'Gold 🥇',
            AffiliateTier::Platinum => 'Platinum 💎',
        };
    }

    private function tierBenefitLine(): string
    {
        return match ($this->newTier) {
            AffiliateTier::Silver   => 'لديك الآن 10 مشتركين أو أكثر — استمر!',
            AffiliateTier::Gold     => 'وصلت إلى 30 مشتركاً — أنت في النخبة!',
            AffiliateTier::Platinum => 'وصلت إلى 100 مشتركاً — أعلى مستوى، أعلى عمولة!',
            default                 => 'واصل الإحالة لترقية مستواك.',
        };
    }
}
