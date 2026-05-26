<?php

namespace App\Modules\CRM\Actions\FollowUp;

use App\Modules\CRM\Enums\ActivityType;
use App\Modules\CRM\Enums\FollowUpStatus;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Support\Facades\DB;

class CompleteFollowUpAction
{
    public function execute(ClientFollowUp $followUp, int $actorId, ?string $notes = null): ClientFollowUp
    {
        if ($followUp->status === FollowUpStatus::Completed) {
            return $followUp;
        }

        return DB::transaction(function () use ($followUp, $actorId, $notes): ClientFollowUp {
            $followUp->update([
                'status'       => FollowUpStatus::Completed->value,
                'completed_at' => now(),
                'notes'        => $notes ?? $followUp->notes,
            ]);

            // تحديث last_contact_at للعميل
            $followUp->client()->update(['last_contact_at' => now()]);

            \App\Modules\CRM\Models\ClientActivity::create([
                'client_id'   => $followUp->client_id,
                'user_id'     => $actorId,
                'type'        => ActivityType::FollowUpCompleted->value,
                'description' => "اكتملت المتابعة: {$followUp->title}",
                'occurred_at' => now(),
            ]);

            return $followUp->refresh();
        });
    }
}
