<?php

namespace App\Modules\CRM\Actions\FollowUp;

use App\Modules\CRM\Enums\FollowUpStatus;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Support\Facades\DB;

class CancelFollowUpAction
{
    public function execute(ClientFollowUp $followUp, int $actorId): ClientFollowUp
    {
        if (in_array($followUp->status, [
            FollowUpStatus::Completed,
            FollowUpStatus::Cancelled,
        ], strict: true)) {
            return $followUp; // لا يمكن إلغاء مكتملة أو ملغاة مسبقاً
        }

        return DB::transaction(function () use ($followUp): ClientFollowUp {
            $followUp->update(['status' => FollowUpStatus::Cancelled->value]);

            return $followUp->refresh();
        });
    }
}
