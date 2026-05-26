<?php

namespace App\Modules\CRM\Actions\FollowUp;

use App\Modules\CRM\DTOs\CreateFollowUpDTO;
use App\Modules\CRM\Enums\ActivityType;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Support\Facades\DB;

class CreateFollowUpAction
{
    public function execute(CreateFollowUpDTO $dto): ClientFollowUp
    {
        return DB::transaction(function () use ($dto): ClientFollowUp {
            $followUp = ClientFollowUp::create($dto->toArray());

            // تسجيل النشاط بعد Commit عبر Event (Sprint 6 يضيف Event كامل)
            // حالياً: تسجيل مباشر بعد Transaction (مؤقت حتى Event layer مكتملة)
            \App\Modules\CRM\Models\ClientActivity::create([
                'client_id'   => $dto->clientId,
                'user_id'     => $dto->userId,
                'type'        => ActivityType::FollowUpCreated->value,
                'description' => "متابعة مجدولة: {$dto->title}",
                'metadata'    => ['due_at' => $dto->dueAt->toDateTimeString()],
                'occurred_at' => now(),
            ]);

            return $followUp;
        });
    }
}
