<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Requests\StoreClientRequest;

final readonly class CreateClientDTO
{
    public function __construct(
        public int     $userId,
        public string  $name,
        public ?string $phone     = null,
        public ?string $email     = null,
        public ?string $company   = null,
        public ?string $position  = null,
        public ?string $website   = null,
        public ?string $address   = null,
        public ?string $city      = null,
        public ?string $country   = null,
        public ?string $notes     = null,
        public ?ClientStatus $status = null,
        public ?ClientSource $source = null,
        public bool    $isActive  = true,
        public array   $tagIds    = [],
    ) {}

    // ==================== Factories ====================

    public static function fromRequest(StoreClientRequest $request): self
    {
        return new self(
            userId:   $request->user()->id,
            name:     $request->string('name')->toString(),
            phone:    $request->filled('phone')   ? $request->string('phone')->toString()   : null,
            email:    $request->filled('email')   ? $request->string('email')->toString()   : null,
            company:  $request->filled('company')  ? $request->string('company')->toString()  : null,
            position: $request->filled('position') ? $request->string('position')->toString() : null,
            website:  $request->filled('website')  ? $request->string('website')->toString()  : null,
            address:  $request->filled('address')  ? $request->string('address')->toString()  : null,
            city:     $request->filled('city')     ? $request->string('city')->toString()     : null,
            country:  $request->filled('country')  ? $request->string('country')->toString()  : null,
            notes:    $request->filled('notes')    ? $request->string('notes')->toString()    : null,
            status:   $request->filled('status')
                          ? ClientStatus::from($request->string('status')->toString())
                          : null,
            source:   $request->filled('source')
                          ? ClientSource::from($request->string('source')->toString())
                          : null,
            isActive: $request->boolean('is_active', true),
            tagIds:   $request->array('tag_ids', []),
        );
    }

    /**
     * إنشاء من صف CSV/Excel أثناء الاستيراد.
     *
     * @param array<string, mixed> $row  الصف المُعالَج بعد column mapping
     * @param int                  $userId
     */
    public static function fromImportRow(array $row, int $userId): self
    {
        $source = isset($row['source']) && ClientSource::tryFrom($row['source'])
            ? ClientSource::from($row['source'])
            : ClientSource::Import;

        return new self(
            userId:   $userId,
            name:     trim((string) ($row['name'] ?? '')),
            phone:    filled($row['phone']   ?? null) ? trim((string) $row['phone'])   : null,
            email:    filled($row['email']   ?? null) ? strtolower(trim((string) $row['email'])) : null,
            company:  filled($row['company'] ?? null) ? trim((string) $row['company']) : null,
            notes:    filled($row['notes']   ?? null) ? trim((string) $row['notes'])   : null,
            status:   ClientStatus::Active,
            source:   $source,
            isActive: true,
            tagIds:   [],
        );
    }

    // ==================== Helpers ====================

    public function toArray(): array
    {
        // نستبعد القيم null صراحةً حتى تعمل قيم DEFAULT في قاعدة البيانات
        // (مثل source DEFAULT 'direct' و status DEFAULT 'active')
        $data = [
            'user_id'   => $this->userId,
            'name'      => $this->name,
            'is_active' => $this->isActive,
        ];

        if ($this->phone    !== null) $data['phone']    = $this->phone;
        if ($this->email    !== null) $data['email']    = $this->email;
        if ($this->company  !== null) $data['company']  = $this->company;
        if ($this->position !== null) $data['position'] = $this->position;
        if ($this->website  !== null) $data['website']  = $this->website;
        if ($this->address  !== null) $data['address']  = $this->address;
        if ($this->city     !== null) $data['city']     = $this->city;
        if ($this->country  !== null) $data['country']  = $this->country;
        if ($this->notes    !== null) $data['notes']    = $this->notes;
        if ($this->status   !== null) $data['status']   = $this->status->value;
        if ($this->source   !== null) $data['source']   = $this->source->value;

        return $data;
    }
}
