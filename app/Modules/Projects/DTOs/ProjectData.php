<?php

namespace App\Modules\Projects\DTOs;

use App\Support\Enums\ProjectType;

class ProjectData
{
    public function __construct(
        public readonly string      $name,
        public readonly ProjectType $type,
        public readonly string      $currency,
        public readonly string      $color,
        public readonly ?string     $description = null,
        public readonly bool        $is_active   = true,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:        $data['name'],
            type:        ProjectType::from($data['type']),
            currency:    $data['currency'],
            color:       $data['color'],
            description: $data['description'] ?? null,
            is_active:   isset($data['is_active']) ? (bool) $data['is_active'] : true,
        );
    }
}
