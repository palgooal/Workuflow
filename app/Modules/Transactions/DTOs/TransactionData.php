<?php

namespace App\Modules\Transactions\DTOs;

use App\Support\Enums\TransactionType;
use Carbon\Carbon;

class TransactionData
{
    public function __construct(
        public readonly TransactionType $type,
        public readonly float           $amount,
        public readonly string          $currency,
        public readonly string          $description,
        public readonly Carbon          $transaction_date,
        public readonly ?string         $project_id   = null,
        public readonly ?string         $category_id  = null,
        public readonly ?string         $notes        = null,
        public readonly ?string         $reference    = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            type:             TransactionType::from($data['type']),
            amount:           (float) $data['amount'],
            currency:         $data['currency'],
            description:      $data['description'],
            transaction_date: Carbon::parse($data['transaction_date']),
            project_id:       $data['project_id']  ?? null,
            category_id:      $data['category_id'] ?? null,
            notes:            $data['notes']        ?? null,
            reference:        $data['reference']    ?? null,
        );
    }
}
