<?php

namespace App\Modules\CRM\Actions\Client;

use App\Models\Client;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Events\ClientArchived;
use Illuminate\Support\Facades\DB;

/**
 * ArchiveClientAction — أرشفة / إلغاء أرشفة عميل
 *
 * الأرشفة ≠ الحذف:
 * - is_archived = true + status = archived
 * - البيانات محفوظة كاملةً
 * - يمكن استعادته في أي وقت
 */
class ArchiveClientAction
{
    public function execute(Client $client, int $actorId, bool $archive = true): Client
    {
        if ($client->is_archived === $archive) {
            return $client; // لا تغيير مطلوب
        }

        return DB::transaction(function () use ($client, $actorId, $archive): Client {
            $client->update([
                'is_archived' => $archive,
                'status'      => $archive
                    ? ClientStatus::Archived->value
                    : ClientStatus::Inactive->value,
            ]);

            event(new ClientArchived($client, $actorId, $archive));

            return $client->refresh();
        });
    }
}
