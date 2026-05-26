<?php

namespace App\Modules\CRM\Actions\Client;

use App\Models\Client;
use App\Modules\CRM\DTOs\CreateClientDTO;
use App\Modules\CRM\Events\ClientCreated;
use Illuminate\Support\Facades\DB;

/**
 * CreateClientAction — إنشاء عميل جديد
 *
 * المسؤوليات:
 * 1. إنشاء سجل العميل داخل Transaction
 * 2. ربط الوسوم الاختيارية
 * 3. إطلاق حدث ClientCreated (يُسجَّل النشاط بـ afterCommit=true)
 */
class CreateClientAction
{
    public function execute(CreateClientDTO $dto): Client
    {
        return DB::transaction(function () use ($dto): Client {
            // 1. إنشاء العميل
            $client = Client::create($dto->toArray());

            // 2. ربط الوسوم إن وُجدت
            if (! empty($dto->tagIds)) {
                $pivot = [];
                foreach ($dto->tagIds as $tagId) {
                    $pivot[(int) $tagId] = [
                        'assigned_by' => $dto->userId,
                        'assigned_at' => now(),
                    ];
                }
                $client->tags()->attach($pivot);
            }

            // 3. تحديث last_contact_at
            $client->update(['last_contact_at' => now()]);

            // 4. إطلاق الحدث — المستمع يسجّل النشاط بعد Commit (C-01)
            event(new ClientCreated($client, $dto->userId));

            return $client->fresh(['tags']);
        });
    }
}
