<?php

namespace App\Modules\CRM\Events;

use App\Modules\CRM\Models\ClientFollowUp;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FollowUpCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ClientFollowUp $followUp,
        public readonly int            $actorId,
    ) {}
}
