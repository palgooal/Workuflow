<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\DTOs\CreateClientDTO;
use App\Modules\CRM\DTOs\UpdateClientDTO;
use App\Modules\CRM\Requests\StoreClientRequest;
use App\Modules\CRM\Requests\UpdateClientRequest;
use App\Modules\CRM\Services\ClientService;
use App\Modules\CRM\Services\ClientTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService    $clientService,
        private readonly ClientTagService $tagService,
    ) {}

    // ==================== List ====================

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $filters = ClientFiltersDTO::fromRequest($request);
        $clients = $this->clientService->listClients(
            userId:  $request->user()->id,
            filters: $filters,
        );

        // طلب AJAX للبحث الفوري / Infinite Scroll
        if ($request->wantsJson()) {
            return response()->json([
                'data'        => $clients->map(fn ($c) => $this->clientToArray($c)),
                'next_cursor' => $clients->nextCursor()?->encode(),
                'has_more'    => $clients->hasMorePages(),
            ]);
        }

        $tags  = $this->tagService->forUser($request->user()->id);
        $stats = $this->clientService->stats($request->user()->id);

        return view('crm.clients.index', compact('clients', 'filters', 'tags', 'stats'));
    }

    private function clientToArray(Client $c): array
    {
        return [
            'id'           => $c->id,
            'public_id'    => $c->public_id,
            'name'         => $c->name,
            'company'      => $c->company,
            'email'        => $c->email,
            'phone'        => $c->phone,
            'is_archived'  => $c->is_archived,
            'status'       => $c->status?->value,
            'status_label' => $c->status?->label(),
            'status_badge' => $c->status?->badgeClass(),
            'health_score' => $c->health_score,
            'last_contact' => $c->last_contact_at?->diffForHumans(),
            'tags'         => $c->tags->map(fn ($t) => [
                'name'  => $t->name,
                'color' => $t->color ?? '#6366f1',
                'icon'  => $t->icon,
            ])->toArray(),
            'show_url'     => route('clients.show', $c->public_id),
            'edit_url'     => route('clients.edit', $c->public_id),
        ];
    }

    // ==================== Create ====================

    public function create(Request $request): View
    {
        $this->authorize('create', Client::class);

        $tags = $this->tagService->forUser($request->user()->id);

        return view('crm.clients.create', compact('tags'));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->clientService->create(
            CreateClientDTO::fromRequest($request)
        );

        return redirect()
            ->route('clients.index')
            ->with('success', "تم إضافة العميل {$client->name} بنجاح. ✓");
    }

    // ==================== Show ====================

    public function show(Request $request, string $publicId): View
    {
        $client = $this->resolveClient($publicId, $request->user()->id);
        $this->authorize('view', $client);

        $client = $this->clientService->findWithRelations($client->id, $request->user()->id);

        $tagSuggestions = $this->tagService->suggest($client);
        $allTags        = $this->tagService->forUser($request->user()->id);

        $projects = \App\Models\Project::where('client_id', $client->id)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        $clientInvoices = \App\Models\Invoice::where('client_id', $client->id)
            ->where('user_id', $request->user()->id)
            ->with('project')
            ->orderByDesc('created_at')
            ->get();

        $clientQuotes = \App\Models\Quote::where('client_id', $client->id)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        // ── حساب الإحصائيات المالية مجمّعة حسب العملة ───────────────
        $activeInvoices = $clientInvoices->whereNotIn(
            'status', [\App\Support\Enums\InvoiceStatus::Cancelled]
        );

        // إيراد لكل عملة: ['ILS' => 5000, 'USD' => 1200]
        $revenueByCurrency = $activeInvoices
            ->groupBy('currency')
            ->map(fn ($g) => $g->sum('total'))
            ->sortByDesc(fn ($v) => $v)
            ->all();

        // مدفوع لكل عملة
        $paidByCurrency = $clientInvoices
            ->where('status', \App\Support\Enums\InvoiceStatus::Paid)
            ->groupBy('currency')
            ->map(fn ($g) => $g->sum('total'))
            ->all();

        // مستحق لكل عملة (الإيراد − المدفوع لنفس العملة)
        $outstandingByCurrency = [];
        foreach ($revenueByCurrency as $cur => $rev) {
            $paid = $paidByCurrency[$cur] ?? 0;
            if ($rev - $paid > 0.009) {
                $outstandingByCurrency[$cur] = $rev - $paid;
            }
        }

        $invoiceCount = $activeInvoices->count();

        // تحديث invoice_count في DB فقط (بدون total_revenue/total_paid المختلطة)
        if ((int) $client->invoice_count !== $invoiceCount) {
            $client->update(['invoice_count' => $invoiceCount]);
        }

        // حساب health_score إذا لم يُحسَب من قبل أو إذا تغيّرت البيانات
        if ($client->health_score === null) {
            try {
                app(\App\Modules\CRM\Services\ClientHealthScoreService::class)->calculate($client);
                $client->refresh();
            } catch (\Throwable) {
                // تجاهل الخطأ — الصفحة ستعمل بدون health_score
            }
        }

        return view('crm.clients.show', compact(
            'client', 'tagSuggestions', 'allTags', 'projects',
            'clientInvoices', 'clientQuotes',
            'revenueByCurrency', 'paidByCurrency', 'outstandingByCurrency'
        ));
    }

    // ==================== Edit / Update ====================

    public function edit(Request $request, string $publicId): View
    {
        $client = $this->resolveClient($publicId, $request->user()->id);
        $this->authorize('update', $client);

        $tags = $this->tagService->forUser($request->user()->id);

        return view('crm.clients.edit', compact('client', 'tags'));
    }

    public function update(UpdateClientRequest $request, string $publicId): RedirectResponse
    {
        $client = $this->resolveClient($publicId, $request->user()->id);

        $updated = $this->clientService->update(
            $client,
            UpdateClientDTO::fromRequest($request, $client)
        );

        return redirect()
            ->route('clients.show', $updated->public_id)
            ->with('success', 'تم تحديث بيانات العميل.');
    }

    // ==================== Delete ====================

    public function destroy(Request $request, string $publicId): RedirectResponse
    {
        $client = $this->resolveClient($publicId, $request->user()->id);
        $this->authorize('delete', $client);

        $name = $client->name;
        $this->clientService->delete($client, $request->user()->id);

        return redirect()
            ->route('clients.index')
            ->with('success', "تم حذف العميل {$name}.");
    }

    // ==================== Archive ====================

    // ==================== Bulk Action ====================

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action'     => ['required', 'in:archive,restore,tag'],
            'client_ids' => ['required', 'array', 'min:1', 'max:200'],
            'tag_id'     => ['nullable', 'integer'],
        ]);

        $action    = $request->input('action');
        $clientIds = $request->input('client_ids');
        $userId    = $request->user()->id;

        $clients = Client::whereIn('id', $clientIds)
            ->where('user_id', $userId)
            ->get();

        foreach ($clients as $client) {
            match ($action) {
                'archive' => $this->clientService->archive($client, $userId),
                'restore' => $this->clientService->restore($client, $userId),
                'tag'     => $request->filled('tag_id')
                    ? $this->tagService->assign($client, [$request->integer('tag_id')], $userId)
                    : null,
                default   => null,
            };
        }

        return response()->json([
            'message' => "تم تطبيق الإجراء على {$clients->count()} عميل.",
            'count'   => $clients->count(),
        ]);
    }

    public function archive(Request $request, string $publicId): RedirectResponse
    {
        $client = $this->resolveClient($publicId, $request->user()->id);
        $this->authorize('archive', $client);

        $this->clientService->archive($client, $request->user()->id);

        return redirect()
            ->route('clients.show', $client->public_id)
            ->with('success', 'تمت أرشفة العميل. يمكنك استعادته في أي وقت.');
    }

    public function restore(Request $request, string $publicId): RedirectResponse
    {
        $client = Client::where('public_id', $publicId)
            ->where('user_id', $request->user()->id)
            ->withTrashed()
            ->firstOrFail();

        $this->authorize('restore', $client);

        $this->clientService->restore($client, $request->user()->id);

        return redirect()
            ->route('clients.show', $client->public_id)
            ->with('success', 'تمت استعادة العميل.');
    }

    // ==================== Timeline + Stats ====================

    public function timeline(Request $request, string $publicId): JsonResponse
    {
        $client = $this->resolveClient($publicId, $request->user()->id);
        $this->authorize('view', $client);

        $perPage = 15;
        $query   = $client->activities()
            ->with('actor:id,name')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        // cursor pagination يدوي عبر id
        if ($cursor = $request->query('cursor')) {
            $query->where('id', '<', (int) $cursor);
        }

        $items = $query->limit($perPage + 1)->get();
        $hasMore    = $items->count() > $perPage;
        $items      = $items->take($perPage);
        $nextCursor = $hasMore ? $items->last()?->id : null;

        $data = $items->map(fn ($a) => [
            'id'          => $a->id,
            'type'        => $a->type->value,
            'icon'        => $a->type->icon(),
            'color'       => $a->type->color(),
            'description' => $a->description,
            'actor'       => $a->actor?->name ?? 'النظام',
            'occurred_ago'=> $a->occurred_at->diffForHumans(),
        ]);

        return response()->json([
            'data'        => $data,
            'has_more'    => $hasMore,
            'next_cursor' => $nextCursor,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        return response()->json(
            $this->clientService->stats($request->user()->id)
        );
    }

    // ==================== Helper ====================

    private function resolveClient(string $publicId, int $userId): Client
    {
        return Client::where('public_id', $publicId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
