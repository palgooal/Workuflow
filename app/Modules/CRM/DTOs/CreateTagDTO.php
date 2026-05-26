<?php

namespace App\Modules\CRM\DTOs;

use App\Modules\CRM\Requests\StoreTagRequest;
use Illuminate\Support\Str;

final readonly class CreateTagDTO
{
    public function __construct(
        public int     $userId,
        public string  $name,
        public string  $color,
        public ?string $icon   = null,
        public ?string $slug   = null,
    ) {}

    // ==================== Factory ====================

    public static function fromRequest(StoreTagRequest $request): self
    {
        $name = $request->string('name')->toString();

        // إنشاء slug من الاسم إذا لم يُرسَل
        $slug = $request->filled('slug')
            ? $request->string('slug')->toString()
            : Str::slug($name);

        return new self(
            userId: $request->user()->id,
            name:   $name,
            color:  $request->string('color')->toString(),
            icon:   $request->filled('icon') ? $request->string('icon')->toString() : null,
            slug:   $slug,
        );
    }

    // ==================== Helpers ====================

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name'    => $this->name,
            'color'   => $this->color,
            'icon'    => $this->icon,
            'slug'    => $this->slug ?? Str::slug($this->name),
        ];
    }
}
