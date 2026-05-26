<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\DTOs\CreateFollowUpDTO;
use App\Modules\CRM\Models\ClientFollowUp;
use App\Modules\CRM\Requests\StoreFollowUpRequest;
use App\Modules\CRM\Services\FollowUpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientFollowUpController extends Controller
{
    public function __construct(
        private readonly FollowUpService $followUpService,
    ) {}

    // ==================== قائمة عامة ====================

    /**
     * لوحة المتابعات — 3 أعمدة: اليوم / الأسبوع / متأخرة
     * Web: Blade View | AJAX: JSON
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $userId = $request->user()->id;

        if ($request->wantsJson()) {
            $followUps = ClientFollowUp::where('user_id', $userId)
                ->with('client:id,name,company,public_id')
                ->orderBy('due_at')
                ->paginate(20);
            return response()->json(['data' => $followUps]);
        }

        // متأخرة (due_at < now, status = pending أو overdue)
        $overdue = ClientFollowUp::where('user_id', $userId)
            ->whereIn('status', ['pending', 'overdue'])
            ->where('due_at', '<', now()->startOfDay())
            ->with('client:id,name,public_id')
            ->orderByDesc('priority')
            ->orderBy('due_at')
            ->get();

        // اليوم
        $today = ClientFollowUp::where('user_id', $userId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_at', today())
            ->with('client:id,name,public_id')
            ->orderByDesc('priority')
            ->orderBy('due_at')
            ->get();

        // هذا الأسبوع (بعد اليوم وحتى نهاية الأسبوع)
        $thisWeek = ClientFollowUp::where('user_id', $userId)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereBetween('due_at', [now()->startOfDay()->addDay(), now()->endOfWeek()])
            ->with('client:id,name,public_id')
            ->orderByDesc('priority')
            ->orderBy('due_at')
            ->get();

        // قائمة العملاء للـ Modal
        $clients = Client::where('user_id', $userId)
            ->where('is_archived', false)
            ->orderBy('name')
            ->get(['id', 'name', 'public_id', 'company']);

        return view('crm.follow-ups.index', compact(
            'overdue', 'today', 'thisWeek', 'clients'
        ));
    }

    /**
     * المتابعات المستحقة خلال 7 أيام (JSON للـ Dashboard widget)
     */
    public function upcoming(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $followUps = $this->followUpService->upcoming($request->user()->id);

        return response()->json(['data' => $followUps]);
    }

    /**
     * إنشاء متابعة من لوحة المتابعات (بدون client في URL)
     * POST /clients/follow-ups/quick
     */
    public function storeGeneral(StoreFollowUpRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $clientId = (int) $request->input('client_id');
        $client   = Client::where('id', $clientId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->authorize('update', $client);

        $followUp = $this->followUpService->create(
            CreateFollowUpDTO::fromRequest($request, $client->id)
        );

        return response()->json([
            'data'    => $followUp->load('client:id,name,public_id'),
            'message' => 'تم إنشاء المتابعة بنجاح.',
        ], 201);
    }

    // ==================== إدارة متابعات عميل محدد ====================

    /**
     * إنشاء متابعة جديدة لعميل
     * Web: redirect back | AJAX: JSON
     */
    public function store(StoreFollowUpRequest $request, string $clientPublicId)
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('update', $client);

        $followUp = $this->followUpService->create(
            CreateFollowUpDTO::fromRequest($request, $client->id)
        );

        if ($request->wantsJson()) {
            return response()->json([
                'data'    => $followUp->load('client:id,name,public_id'),
                'message' => 'تم إنشاء المتابعة بنجاح.',
            ], 201);
        }

        return redirect()
            ->route('clients.show', $client->public_id)
            ->with('success', 'تم إنشاء المتابعة بنجاح.')
            ->withFragment('followups');
    }

    /**
     * إتمام متابعة
     * Web: redirect back | AJAX: JSON
     */
    public function complete(Request $request, string $clientPublicId, ClientFollowUp $followUp)
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('update', $client);

        $this->ensureFollowUpBelongsToClient($followUp, $client->id);

        $followUp = $this->followUpService->complete(
            followUp: $followUp,
            actorId:  $request->user()->id,
            notes:    $request->input('notes'),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'data'    => $followUp,
                'message' => 'تم إتمام المتابعة.',
            ]);
        }

        return redirect()
            ->route('clients.show', $client->public_id)
            ->with('success', 'تم إتمام المتابعة.')
            ->withFragment('followups');
    }

    /**
     * إلغاء متابعة
     * Web: redirect back | AJAX: JSON
     */
    public function cancel(Request $request, string $clientPublicId, ClientFollowUp $followUp)
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('update', $client);

        $this->ensureFollowUpBelongsToClient($followUp, $client->id);

        $followUp = $this->followUpService->cancel($followUp, $request->user()->id);

        if ($request->wantsJson()) {
            return response()->json([
                'data'    => $followUp,
                'message' => 'تم إلغاء المتابعة.',
            ]);
        }

        return redirect()
            ->route('clients.show', $client->public_id)
            ->with('success', 'تم إلغاء المتابعة.')
            ->withFragment('followups');
    }

    // ==================== Helpers ====================

    private function resolveClient(string $publicId, int $userId): Client
    {
        return Client::where('public_id', $publicId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    private function ensureFollowUpBelongsToClient(ClientFollowUp $followUp, int $clientId): void
    {
        if ($followUp->client_id !== $clientId) {
            abort(404);
        }
    }
}
