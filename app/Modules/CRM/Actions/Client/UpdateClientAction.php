<?php

namespace App\Modules\CRM\Actions\Client;

use App\Models\Client;
use App\Modules\CRM\DTOs\UpdateClientDTO;
use App\Modules\CRM\Events\ClientUpdated;
use Illuminate\Support\Facades\DB;

/**
 * UpdateClientAction — تعديل بيانات العميل
 *
 * - يُعدَّل فقط الحقول التي تغيّرت (UpdateClientDTO::toChangedArray)
 * - يُطلق ClientUpdated بعد Commit (C-01)
 */
class UpdateClientAction
{
    public function execute(Client $client, UpdateClientDTO $dto): Client
    {
        $changes = $dto->toChangedArray();

        if (empty($changes)) {
            return $client; // لا تغييرات — تجنّب DB write غير ضروري
        }

        return DB::transaction(function () use ($client, $changes, $dto): Client {
            $before = $client->only(array_keys($changes));

            $client->update($changes);

            event(new ClientUpdated($client, $before, $changes, $dto));

            return $client->refresh();
        });
    }
}
