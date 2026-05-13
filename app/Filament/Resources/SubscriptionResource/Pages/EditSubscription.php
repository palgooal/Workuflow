<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use App\Support\Enums\SubscriptionPlan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // مزامنة subscription_plan في جدول users عند التعديل
        $subscription = $this->record->fresh();
        $plan = $subscription->plan instanceof SubscriptionPlan
            ? $subscription->plan
            : SubscriptionPlan::tryFrom($subscription->plan);

        if ($plan && $subscription->status === 'active') {
            $subscription->user->update(['subscription_plan' => $plan]);
        } elseif ($subscription->status === 'cancelled') {
            $subscription->user->update(['subscription_plan' => SubscriptionPlan::Free]);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الاشتراك بنجاح';
    }
}
