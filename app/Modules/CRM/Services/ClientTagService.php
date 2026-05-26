<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Actions\Tag\AssignTagAction;
use App\Modules\CRM\Actions\Tag\BulkTagAction;
use App\Modules\CRM\Actions\Tag\RemoveTagAction;
use App\Modules\CRM\DTOs\BulkTagDTO;
use App\Modules\CRM\DTOs\CreateTagDTO;
use App\Modules\CRM\Enums\TagType;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ClientTagService
{
    public function __construct(
        private readonly AssignTagAction $assignAction,
        private readonly RemoveTagAction $removeAction,
        private readonly BulkTagAction   $bulkAction,
    ) {}

    // ==================== CRUD ====================

    public function create(CreateTagDTO $dto): ClientTag
    {
        $slug = $dto->slug ?? Str::slug($dto->name);

        // ضمان فرادة الـ slug للمستخدم
        $slug = $this->uniqueSlug($slug, $dto->userId);

        $tag = ClientTag::create([
            'user_id'   => $dto->userId,
            'name'      => $dto->name,
            'slug'      => $slug,
            'color'     => $dto->color,
            'icon'      => $dto->icon,
            'type'      => TagType::Custom->value,
            'is_active' => true,
            'priority'  => $this->nextPriority($dto->userId),
        ]);

        $this->clearCache($dto->userId);

        return $tag;
    }

    public function update(ClientTag $tag, array $data): ClientTag
    {
        $tag->update(array_filter([
            'name'  => $data['name']  ?? null,
            'color' => $data['color'] ?? null,
            'icon'  => $data['icon']  ?? null,
        ]));

        $this->clearCache($tag->user_id);

        return $tag->refresh();
    }

    public function delete(ClientTag $tag): void
    {
        if (! $tag->isDeletable()) {
            throw new \RuntimeException("وسوم النظام لا يمكن حذفها.");
        }

        $userId = $tag->user_id;
        $tag->clients()->detach(); // إزالة من جميع العملاء أولاً
        $tag->delete();

        $this->clearCache($userId);
    }

    // ==================== Assignment ====================

    public function assign(Client $client, ClientTag $tag, int $actorId): void
    {
        $this->assignAction->execute($client, $tag, $actorId);
        $this->clearCache($client->user_id);
    }

    public function remove(Client $client, ClientTag $tag, int $actorId): void
    {
        $this->removeAction->execute($client, $tag, $actorId);
        $this->clearCache($client->user_id);
    }

    public function bulk(BulkTagDTO $dto): array
    {
        $result = $this->bulkAction->execute($dto);
        $this->clearCache($dto->userId);
        return $result;
    }

    // ==================== Queries ====================

    /**
     * وسوم المستخدم (custom + system) — C-06: key-based cache
     */
    public function forUser(int $userId): Collection
    {
        $ttl = config('crm.cache.tags_ttl', 3600);
        $key = "crm:tags:user:{$userId}";

        return Cache::remember($key, $ttl, fn () =>
            ClientTag::query()->forUser($userId)->get()
        );
    }

    /**
     * اقتراح وسوم بناءً على بيانات العميل (قاعدة بيانات — بدون ML)
     * Sprint 5 يُكمل النسخة الذكية
     */
    public function suggest(Client $client): Collection
    {
        $suggestions = collect();
        $existingIds = $client->tags->pluck('id')->toArray();

        // اقتراح "New Client" للعملاء المنشأين خلال 30 يوماً
        if ($client->created_at->diffInDays(now()) <= 30) {
            $tag = ClientTag::where('slug', 'new-client')->whereNull('user_id')->first();
            if ($tag && ! in_array($tag->id, $existingIds, true)) {
                $suggestions->push($tag);
            }
        }

        // اقتراح "VIP" للعملاء ذوي الإيراد العالي
        if ((float) $client->total_revenue >= 5000) {
            $tag = ClientTag::where('slug', 'vip')->whereNull('user_id')->first();
            if ($tag && ! in_array($tag->id, $existingIds, true)) {
                $suggestions->push($tag);
            }
        }

        // اقتراح "Late Payer" إذا كان outstanding > 0 وآخر دفعة منذ أكثر من 30 يوماً
        $outstanding = (float) $client->total_revenue - (float) $client->total_paid;
        if ($outstanding > 0 && $client->last_payment_at?->diffInDays(now()) > 30) {
            $tag = ClientTag::where('slug', 'late-payer')->whereNull('user_id')->first();
            if ($tag && ! in_array($tag->id, $existingIds, true)) {
                $suggestions->push($tag);
            }
        }

        // اقتراح "Inactive" إذا لم يكن هناك تواصل منذ 60 يوماً
        if ($client->last_contact_at?->diffInDays(now()) > 60) {
            $tag = ClientTag::where('slug', 'inactive')->whereNull('user_id')->first();
            if ($tag && ! in_array($tag->id, $existingIds, true)) {
                $suggestions->push($tag);
            }
        }

        return $suggestions;
    }

    // ==================== Helpers ====================

    private function uniqueSlug(string $slug, int $userId): string
    {
        $original = $slug;
        $counter  = 1;

        while (ClientTag::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function nextPriority(int $userId): int
    {
        $max = ClientTag::where('user_id', $userId)->max('priority') ?? 0;
        return (int) $max + 1;
    }

    private function clearCache(int $userId): void
    {
        Cache::forget("crm:tags:user:{$userId}");
    }
}
