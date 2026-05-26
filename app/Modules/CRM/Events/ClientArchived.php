<?php

namespace App\Modules\CRM\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientArchived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Client $client,
        public readonly int    $actorId,
        public readonly bool   $archived, // true=أرشفة، false=استعادة
    ) {}
}
