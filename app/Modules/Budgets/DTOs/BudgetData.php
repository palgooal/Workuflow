<?php

namespace App\Modules\Budgets\DTOs;

class BudgetData
{
    public function __construct(
        public readonly float   $amount,
        public readonly string  $period,   // monthly | yearly
        public readonly int     $year,
        public readonly ?int    $month      = null,
        public readonly ?string $category_id = null,
        public readonly ?string $project_id  = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            amount:      (float) $data['amount'],
            period:      $data['period'],
            year:        (int)   $data['year'],
            month:       isset($data['month']) && $data['period'] === 'monthly'
                            ? (int) $data['month']
                            : null,
            category_id: $data['category_id'] ?? null,
            project_id:  $data['project_id']  ?? null,
        );
    }
}
