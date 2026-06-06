<?php

namespace App\Modules\Projects\DTOs;

use App\Support\Enums\ProjectStatus;
use App\Support\Enums\ProjectType;

class ProjectData
{
    public function __construct(
        public readonly string        $name,
        public readonly ProjectType   $type,
        public readonly string        $currency,
        public readonly string        $color,
        public readonly ?string       $description    = null,
        public readonly ProjectStatus $status         = ProjectStatus::Active,
        public readonly ?int          $client_id      = null,
        public readonly ?float        $contract_value = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:           $data['name'],
            type:           ProjectType::from($data['type']),
            currency:       $data['currency'],
            color:          $data['color'],
            description:    $data['description'] ?? null,
            status:         ProjectStatus::tryFrom($data['status'] ?? '') ?? ProjectStatus::Active,
            client_id:      isset($data['client_id']) ? (int) $data['client_id'] : null,
            contract_value: isset($data['contract_value']) && $data['contract_value'] !== ''
                                ? (float) $data['contract_value'] : null,
        );
    }
}
