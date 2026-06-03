<?php

namespace App\Modules\CRM\Actions\FollowUp;

use App\Modules\CRM\Enums\FollowUpStatus;
use App\Modules\CRM\Events\FollowUpCompleted;
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

            // C-01 Fix: إطلاق Event بعد Commit — Listener يكتب النشاط بأمان
            event(new FollowUpCompleted($followUp->refresh(), $actorId));

            return $followUp->refresh();
        });
    }
}
