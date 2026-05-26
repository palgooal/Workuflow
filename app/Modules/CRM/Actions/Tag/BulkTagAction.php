<?php

namespace App\Modules\CRM\Actions\Tag;

use App\Models\Client;
use App\Modules\CRM\DTOs\BulkTagDTO;
use App\Modules\CRM\Events\ClientTagAssigned;
use App\Modules\CRM\Events\ClientTagRemoved;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Facades\DB;

/**
 * BulkTagAction — تعيين/إزالة وسوم على مجموعة عملاء
 *
 * - يُعالج العملاء في chunks لتجنّب memory overflow
 * - يتحقق من ملكية كل عميل داخل الـ loop
 * - يُطلق Events لكل عميل + وسم (للتسجيل في الأنشطة)
 */
class BulkTagAction
{
    private const CHUNK_SIZE = 100;

    public function execute(BulkTagDTO $dto): array
    {
        // تحقق من وجود الوسوم
        $tags = ClientTag::whereIn('id', $dto->tagIds)->get()->keyBy('id');

        $results = [
            'processed' => 0,
            'skipped'   => 0,
            'errors'    => [],
        ];

        // معالجة العملاء بـ chunks
        Client::where('user_id', $dto->userId)
            ->whereIn('id', $dto->clientIds)
            ->whereNull('deleted_at')
            ->select('id')
            ->chunkById(self::CHUNK_SIZE, function ($clients) use ($dto, $tags, &$results) {
                foreach ($clients as $client) {
                    try {
                        DB::transaction(function () use ($client, $dto, $tags, &$results) {
                            $fullClient = Client::find($client->id);

                            foreach ($tags as $tag) {
                                if ($dto->isAssign()) {
                                    $alreadyAssigned = $fullClient->tags()
                                        ->where('client_tags.id', $tag->id)
                                        ->exists();

                                    if (! $alreadyAssigned) {
                                        $fullClient->tags()->attach($tag->id, [
                                            'assigned_by' => $dto->userId,
                                            'assigned_at' => now(),
                                        ]);
                                        event(new ClientTagAssigned($fullClient, $tag, $dto->userId));
                                    }
                                } else {
                                    $fullClient->tags()->detach($tag->id);
                                    event(new ClientTagRemoved($fullClient, $tag, $dto->userId));
                                }
                            }
                        });

                        $results['processed']++;
                    } catch (\Throwable $e) {
                        $results['skipped']++;
                        $results['errors'][] = "client #{$client->id}: " . $e->getMessage();
                    }
                }
            });

        return $results;
    }
}
