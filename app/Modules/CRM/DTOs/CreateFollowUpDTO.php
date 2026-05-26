<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Requests\StoreFollowUpRequest;
use Carbon\Carbon;

final readonly class CreateFollowUpDTO
{
    public function __construct(
        public int     $userId,
        public int     $clientId,
        public string  $title,
        public Carbon  $dueAt,
        public ?string $notes      = null,
        public int     $priority   = 3,
        public ?Carbon $reminderAt = null,
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(StoreFollowUpRequest $request, ?int $clientId = null): self
    {
        return new self(
            userId:     $request->user()->id,
            clientId:   $clientId ?? (int) $request->input('client_id'),
            title:      $request->string('title')->toString(),
            dueAt:      Carbon::parse($request->input('due_at')),
            notes:      $request->filled('notes') ? $request->string('notes')->toString() : null,
            priority:   $request->filled('priority') ? (int) $request->input('priority') : 3,
            reminderAt: $request->filled('reminder_at')
                            ? Carbon::parse($request->input('reminder_at'))
                            : null,
        );
    }

    // ==================== Helpers ====================

    public function toArray(): array
    {
        return [
            'user_id'     => $this->userId,
            'client_id'   => $this->clientId,
            'title'       => $this->title,
            'notes'       => $this->notes,
            'due_at'      => $this->dueAt->toDateTimeString(),
            'priority'    => $this->priority,
            'reminder_at' => $this->reminderAt?->toDateTimeString(),
            'status'      => 'pending',
        ];
    }
}
