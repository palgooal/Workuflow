<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type?->value,
            'party_name'       => $this->party_name,
            'amount'           => (float) $this->amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'paid_percentage'  => $this->paidPercentage(),
            'currency'         => $this->currency,
            'status'           => $this->status?->value,
            'due_date'         => $this->due_date?->toDateString(),
            'is_overdue'       => $this->isOverdue(),
            'notes'            => $this->notes,
            'project'          => $this->whenLoaded('project', fn() => [
                'id'   => $this->project?->id,
                'name' => $this->project?->name,
            ]),
            'created_at'       => $this->created_at?->toIso8601String(),
            'updated_at'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
