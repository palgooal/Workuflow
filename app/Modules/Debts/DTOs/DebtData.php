<?php

namespace App\Modules\Debts\DTOs;

use App\Support\Enums\DebtType;
use Carbon\Carbon;

class DebtData
{
    public function __construct(
        public readonly DebtType    $type,
        public readonly string      $party_name,
        public readonly float       $amount,
        public readonly string      $currency,
        public readonly ?Carbon     $due_date    = null,
        public readonly ?string     $notes       = null,
        public readonly ?string     $project_id  = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            type:       DebtType::from($data['type']),
            party_name: $data['party_name'],
            amount:     (float) $data['amount'],
            currency:   $data['currency'],
            due_date:   isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            notes:      $data['notes']       ?? null,
            project_id: $data['project_id']  ?? null,
        );
    }
}
