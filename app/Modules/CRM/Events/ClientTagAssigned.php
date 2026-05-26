<?php

namespace App\Modules\CRM\Events;

use App\Models\Client;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientTagAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Client    $client,
        public readonly ClientTag $tag,
        public readonly int       $actorId,
    ) {}
}
