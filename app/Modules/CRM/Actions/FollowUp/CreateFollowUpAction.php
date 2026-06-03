<?php

namespace App\Modules\CRM\Actions\FollowUp;

use App\Modules\CRM\DTOs\CreateFollowUpDTO;
use App\Modules\CRM\Events\FollowUpCreated;
use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Support\Facades\DB;

class CreateFollowUpAction
{
    public function execute(CreateFollowUpDTO $dto): ClientFollowUp
    {
        return DB::transaction(function () use ($dto): ClientFollowUp {
            $followUp = ClientFollowUp::create($dto->toArray());

            // C-01 Fix: إطلاق Event بعد Commit — Listener يكتب النشاط بأمان
            event(new FollowUpCreated($followUp, $dto->userId));

            return $followUp;
        });
    }
}
