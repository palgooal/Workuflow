<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Modules\Billing\Services\SubscriptionService;
use App\Support\Enums\SubscriptionPlan;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // مزامنة subscription_plan في جدول users
        $subscription = $this->record;
        $plan = $subscription->plan instanceof SubscriptionPlan
            ? $subscription->plan
            : SubscriptionPlan::tryFrom($subscription->plan);

        if ($plan && $subscription->status === 'active') {
            $subscription->user->update(['subscription_plan' => $plan]);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء الاشتراك بنجاح';
    }
}
