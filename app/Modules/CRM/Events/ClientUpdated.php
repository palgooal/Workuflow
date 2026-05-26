<?php

namespace App\Modules\CRM\Events;

use App\Models\Client;
use App\Modules\CRM\DTOs\UpdateClientDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Client          $client,
        public readonly array           $before,   // قيم ما قبل التعديل
        public readonly array           $after,    // الحقول التي تغيّرت فقط
        public readonly UpdateClientDTO $dto,
    ) {}

    public function changedFields(): array
    {
        return array_keys($this->after);
    }
}
