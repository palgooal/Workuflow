<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Requests\BulkTagRequest;

final readonly class BulkTagDTO
{
    public const ACTION_ASSIGN = 'assign';
    public const ACTION_REMOVE = 'remove';

    public function __construct(
        public int    $userId,
        public array  $clientIds,   // int[]
        public array  $tagIds,      // int[]
        public string $action,      // 'assign' | 'remove'
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(BulkTagRequest $request): self
    {
        return new self(
            userId:    $request->user()->id,
            clientIds: array_map('intval', $request->array('client_ids')),
            tagIds:    array_map('intval', $request->array('tag_ids')),
            action:    $request->string('action')->toString(),
        );
    }

    // ==================== Helpers ====================

    public function isAssign(): bool
    {
        return $this->action === self::ACTION_ASSIGN;
    }

    public function isRemove(): bool
    {
        return $this->action === self::ACTION_REMOVE;
    }

    public function clientCount(): int
    {
        return count($this->clientIds);
    }

    public function tagCount(): int
    {
        return count($this->tagIds);
    }
}
