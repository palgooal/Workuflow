<?php

namespace App\Modules\CRM\Actions\Client;

use App\Models\Client;
use App\Modules\CRM\Events\ClientDeleted;
use Illuminate\Support\Facades\DB;

/**
 * DeleteClientAction — حذف ناعم للعميل (SoftDelete)
 *
 * الحذف الدائم (forceDelete) يتطلب صلاحية منفصلة ولا يُنفَّذ هنا تلقائياً.
 */
class DeleteClientAction
{
    public function execute(Client $client, int $actorId): void
    {
        DB::transaction(function () use ($client, $actorId): void {
            // إطلاق الحدث قبل الحذف (نحتاج بيانات العميل)
            event(new ClientDeleted($client, $actorId));

            $client->delete(); // SoftDelete — يضبط deleted_at
        });
    }

    /**
     * حذف دائم — يُستخدم فقط من Admin أو عند حذف حساب المستخدم كاملاً
     */
    public function forceExecute(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            // حذف الملفات المرفقة أولاً
            foreach ($client->attachments as $attachment) {
                \Illuminate\Support\Facades\Storage::disk($attachment->disk)
                    ->delete($attachment->path);
            }

            $client->forceDelete();
        });
    }
}
