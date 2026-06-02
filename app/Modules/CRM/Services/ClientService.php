<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Actions\Client\ArchiveClientAction;
use App\Modules\CRM\Actions\Client\CreateClientAction;
use App\Modules\CRM\Actions\Client\DeleteClientAction;
use App\Modules\CRM\Actions\Client\UpdateClientAction;
use App\Modules\CRM\Builders\ClientQueryBuilder;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\DTOs\CreateClientDTO;
use App\Modules\CRM\DTOs\UpdateClientDTO;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * ClientService — واجهة الأعمال الموحّدة للعملاء
 *
 * يُنسّق بين Actions وQueryBuilder ويُوفّر API بسيطاً للـ Controllers.
 * لا يحتوي على منطق مباشر — يُفوّض للـ Actions.
 */
class ClientService
{
    public function __construct(
        private readonly CreateClientAction  $createAction,
        private readonly UpdateClientAction  $updateAction,
        private readonly ArchiveClientAction $archiveAction,
        private readonly DeleteClientAction  $deleteAction,
    ) {}

    // ==================== CRUD ====================

    public function create(CreateClientDTO $dto): Client
    {
        return $this->createAction->execute($dto);
    }

    public function update(Client $client, UpdateClientDTO $dto): Client
    {
        return $this->updateAction->execute($client, $dto);
    }

    public function archive(Client $client, int $actorId): Client
    {
        return $this->archiveAction->execute($client, $actorId, archive: true);
    }

    public function restore(Client $client, int $actorId): Client
    {
        return $this->archiveAction->execute($client, $actorId, archive: false);
    }

    public function delete(Client $client, int $actorId): void
    {
        $this->deleteAction->execute($client, $actorId);
    }

    // ==================== Query ====================

    /**
     * قائمة العملاء مع الفلاتر والـ Cursor Pagination
     */
    public function countClients(int $userId, ClientFiltersDTO $filters): int
    {
        return (new ClientQueryBuilder($userId))
            ->applyFilters($filters)
            ->count();
    }

    public function listClients(int $userId, ClientFiltersDTO $filters): CursorPaginator
    {
        return (new ClientQueryBuilder($userId))
            ->applyFilters($filters)
            ->withRelations()
            ->cursorPaginate($filters->perPage);
    }

    /**
     * عميل واحد مع كامل علاقاته (صفحة الـ Profile 360°)
     */
    public function findWithRelations(int $clientId, int $userId): ?Client
    {
        return Client::where('id', $clientId)
            ->where('user_id', $userId)
            ->with([
                'tags',
                'latestHealthScore',
                'pendingFollowUps',
                'activities'  => fn ($q) => $q->limit(20),
                'attachments' => fn ($q) => $q->limit(10),
                'customFields.definition',
            ])
            ->first();
    }

    /**
     * إحصائيات سريعة للـ Dashboard
     */
    public function stats(int $userId): array
    {
        $base = Client::where('user_id', $userId)->whereNull('deleted_at');

        return [
            'total'          => (clone $base)->where('is_archived', false)->count(),
            'active'         => (clone $base)->where('status', 'active')->where('is_archived', false)->count(),
            'prospects'      => (clone $base)->where('status', 'prospect')->where('is_archived', false)->count(),
            'archived'       => (clone $base)->where('is_archived', true)->count(),
            'with_follow_up' => \Illuminate\Support\Facades\Schema::hasTable('client_follow_ups')
                ? (clone $base)
                    ->where('is_archived', false)
                    ->whereHas('followUps', fn ($q) => $q->whereIn('status', ['pending', 'overdue']))
                    ->count()
                : 0,
        ];
    }

    /**
     * تحديث last_contact_at (يُستدعى من Listener أو Controller عند أي تفاعل)
     */
    public function touchLastContact(Client $client): void
    {
        $client->touchLastContact();
    }
}
