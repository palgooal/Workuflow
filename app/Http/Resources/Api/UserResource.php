<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'currency'          => $this->currency,
            'timezone'          => $this->timezone,
            'subscription_plan' => $this->subscription_plan?->value,
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
