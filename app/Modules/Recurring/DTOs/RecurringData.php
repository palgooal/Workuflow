<?php

namespace App\Modules\Recurring\DTOs;

use App\Support\Enums\RecurringFrequency;
use App\Support\Enums\TransactionType;

class RecurringData
{
    public function __construct(
        public readonly TransactionType    $type,
        public readonly float              $amount,
        public readonly string             $description,
        public readonly RecurringFrequency $frequency,
        public readonly string             $start_date,
        public readonly ?string            $end_date     = null,
        public readonly ?string            $category_id  = null,
        public readonly ?string            $project_id   = null,
        public readonly string             $currency     = 'SAR',
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            type:        TransactionType::from($data['type']),
            amount:      (float) $data['amount'],
            description: $data['description'],
            frequency:   RecurringFrequency::from($data['frequency']),
            start_date:  $data['start_date'],
            end_date:    $data['end_date'] ?? null,
            category_id: $data['category_id'] ?? null,
            project_id:  $data['project_id'] ?? null,
            currency:    $data['currency'] ?? 'SAR',
        );
    }
}
