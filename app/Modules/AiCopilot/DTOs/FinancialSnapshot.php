<?php

namespace App\Modules\AiCopilot\DTOs;

use JsonSerializable;

final readonly class FinancialSnapshot implements JsonSerializable
{
    public const SCHEMA_VERSION = '1.0';

    public function __construct(
        public array $currencies,
        public array $dataQuality,
    ) {}

    public function toArray(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'data_quality' => $this->dataQuality,
            'currencies' => $this->currencies,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
