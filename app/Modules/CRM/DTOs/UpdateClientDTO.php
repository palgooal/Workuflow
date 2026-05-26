<?php

namespace App\Modules\CRM\DTOs;

use App\Models\Client;
use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Requests\UpdateClientRequest;

final readonly class UpdateClientDTO
{
    public function __construct(
        public ?string $name       = null,
        public ?string $phone      = null,
        public ?string $email      = null,
        public ?string $company    = null,
        public ?string $notes      = null,
        public ?ClientStatus $status  = null,
        public ?ClientSource $source  = null,
        public ?bool   $isActive   = null,
        public ?bool   $isArchived = null,
        public ?string $position   = null,
        public ?string $website    = null,
        public ?string $address    = null,
        public ?string $city       = null,
        public ?string $country    = null,
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(UpdateClientRequest $request, Client $client): self
    {
        return new self(
            name:       $request->has('name')       ? $request->string('name')->toString()       : null,
            phone:      $request->has('phone')      ? ($request->filled('phone') ? $request->string('phone')->toString() : null) : null,
            email:      $request->has('email')      ? ($request->filled('email') ? $request->string('email')->toString() : null) : null,
            company:    $request->has('company')    ? ($request->filled('company') ? $request->string('company')->toString() : null) : null,
            notes:      $request->has('notes')      ? ($request->filled('notes') ? $request->string('notes')->toString() : null) : null,
            status:     $request->has('status') && $request->filled('status')
                            ? ClientStatus::from($request->string('status')->toString())
                            : null,
            source:     $request->has('source') && $request->filled('source')
                            ? ClientSource::from($request->string('source')->toString())
                            : null,
            isActive:   $request->has('is_active')   ? $request->boolean('is_active')   : null,
            isArchived: $request->has('is_archived') ? $request->boolean('is_archived') : null,
            position:   $request->has('position') ? ($request->filled('position') ? $request->string('position')->toString() : null) : null,
            website:    $request->has('website')  ? ($request->filled('website')  ? $request->string('website')->toString()  : null) : null,
            address:    $request->has('address')  ? ($request->filled('address')  ? $request->string('address')->toString()  : null) : null,
            city:       $request->has('city')     ? ($request->filled('city')     ? $request->string('city')->toString()     : null) : null,
            country:    $request->has('country')  ? ($request->filled('country')  ? $request->string('country')->toString()  : null) : null,
        );
    }

    // ==================== Helpers ====================

    /**
     * يُرجع فقط الحقول التي تغيّرت (لاستخدامها في Model::update())
     */
    public function toChangedArray(): array
    {
        $data = [];

        if ($this->name       !== null) $data['name']        = $this->name;
        if ($this->phone      !== null) $data['phone']       = $this->phone;
        if ($this->email      !== null) $data['email']       = $this->email;
        if ($this->company    !== null) $data['company']     = $this->company;
        if ($this->notes      !== null) $data['notes']       = $this->notes;
        if ($this->status     !== null) $data['status']      = $this->status->value;
        if ($this->source     !== null) $data['source']      = $this->source->value;
        if ($this->isActive   !== null) $data['is_active']   = $this->isActive;
        if ($this->isArchived !== null) $data['is_archived'] = $this->isArchived;
        if ($this->position   !== null) $data['position']   = $this->position;
        if ($this->website    !== null) $data['website']    = $this->website;
        if ($this->address    !== null) $data['address']    = $this->address;
        if ($this->city       !== null) $data['city']       = $this->city;
        if ($this->country    !== null) $data['country']    = $this->country;

        return $data;
    }

    public function isEmpty(): bool
    {
        return empty($this->toChangedArray());
    }
}
