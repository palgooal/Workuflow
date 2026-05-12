<?php

namespace App\Modules\Categories\DTOs;

use App\Support\Enums\TransactionType;

class CategoryData
{
    public function __construct(
        public readonly string          $name,
        public readonly TransactionType $type,
        public readonly string          $color,
        public readonly string          $icon,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:  $data['name'],
            type:  TransactionType::from($data['type']),
            color: $data['color'],
            icon:  $data['icon'],
        );
    }
}
