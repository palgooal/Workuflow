<?php

namespace App\Modules\CRM\Actions\Client;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\PaymentCollection;
use App\Models\Quote;
use App\Models\User;

/**
 * DeleteUnlinkedClientsForUserAction
 *
 * يُستخدم عند معالجة طلب حذف بيانات مستخدم (Admin → deleteData)، وينفّذ حرفياً
 * ما تَعِد به الوثائق القانونية:
 * - docs/legal/Data-Deletion.md §3
 * - docs/legal/Privacy-Policy.md §8
 *
 * القاعدة:
 * - عميل CRM غير مرتبط بأي فاتورة، أو عرض سعر، أو سجل تحصيل دفع (سجل مالي
 *   خاضع لسياسة الاحتفاظ) → يُحذف (Soft Delete عبر DeleteClientAction).
 * - عميل مرتبط بأي من هذه السجلات (حتى لو كان السجل نفسه محذوفاً ناعماً،
 *   لأنه لا يزال محفوظاً فعلياً ضمن سياسة الاحتفاظ) → يُستثنى ويبقى كما هو.
 *
 * الحذف يتم عميلاً بعميل عبر DeleteClientAction الموجود مسبقاً — وليس عبر
 * استعلام حذف جماعي (Bulk Delete) — للحفاظ على أحداث الموديل (ClientDeleted)
 * وأي منطق حالي أو مستقبلي مرتبط بها، ولتفادي حذف غير آمن يتجاوز طبقة الموديل.
 */
class DeleteUnlinkedClientsForUserAction
{
    public function __construct(
        private readonly DeleteClientAction $deleteClientAction,
    ) {
    }

    /**
     * @return array{
     *     deleted_count: int,
     *     retained_count: int,
     *     deleted_client_ids: array<int, string>,
     *     retained_client_ids: array<int, string>,
     * }
     */
    public function execute(User $user, int $actorId): array
    {
        $deletedIds  = [];
        $retainedIds = [];

        Client::query()
            ->where('user_id', $user->id)
            ->get()
            ->each(function (Client $client) use ($actorId, &$deletedIds, &$retainedIds): void {
                if ($this->isLinkedToFinancialRecord($client)) {
                    $retainedIds[] = $client->public_id;

                    return;
                }

                $this->deleteClientAction->execute($client, $actorId);
                $deletedIds[] = $client->public_id;
            });

        $result = [
            'deleted_count'       => count($deletedIds),
            'retained_count'      => count($retainedIds),
            'deleted_client_ids'  => $deletedIds,
            'retained_client_ids' => $retainedIds,
        ];

        ActivityLog::record(
            eventType:  'data_deletion.crm_clients_processed',
            userId:     $user->id,
            entityType: 'user',
            entityId:   (string) $user->id,
            metadata:   array_merge($result, [
                'reason'   => 'user_data_deletion_request',
                'actor_id' => $actorId,
            ]),
        );

        return $result;
    }

    /**
     * عميل "مرتبط بسجل مالي" إذا كان له فاتورة، أو عرض سعر، أو سجل تحصيل دفع —
     * حتى لو كانت الفاتورة/العرض محذوفاً ناعماً (withTrashed)، لأن السجل المالي
     * نفسه لا يزال محفوظاً فعلياً ضمن مدة الاحتفاظ المعتمَدة.
     */
    private function isLinkedToFinancialRecord(Client $client): bool
    {
        return Invoice::withTrashed()->where('client_id', $client->id)->exists()
            || Quote::withTrashed()->where('client_id', $client->id)->exists()
            || PaymentCollection::where('client_id', $client->id)->exists();
    }
}
