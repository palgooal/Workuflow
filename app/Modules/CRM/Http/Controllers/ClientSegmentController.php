<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use App\Modules\CRM\Enums\HealthScoreGrade;
use App\Modules\CRM\Models\ClientTag;
use App\Modules\CRM\Models\SavedSegment;
use App\Modules\CRM\Services\ClientHealthScoreService;
use App\Modules\CRM\Services\SavedSegmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientSegmentController extends Controller
{
    public function __construct(
        private readonly SavedSegmentService      $segmentService,
        private readonly ClientHealthScoreService $healthService,
    ) {}

    /**
     * صفحة الشرائح الرئيسية (Web) أو JSON للـ AJAX
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $userId = $request->user()->id;

        if ($request->wantsJson()) {
            $segments = $this->segmentService->forUser($userId);
            return response()->json(['data' => $segments]);
        }

        // ==================== بيانات الشرائح ====================
        $segments = SavedSegment::query()->forUser($userId)->get();

        // ==================== بيانات Health Score ====================
        $healthBase = Client::where('user_id', $userId)
            ->where('is_archived', false)
            ->whereNotNull('health_score');

        $distribution = (clone $healthBase)
            ->selectRaw("
                COUNT(CASE WHEN health_score >= 80 THEN 1 END) as excellent,
                COUNT(CASE WHEN health_score >= 60 AND health_score < 80 THEN 1 END) as good,
                COUNT(CASE WHEN health_score >= 40 AND health_score < 60 THEN 1 END) as fair,
                COUNT(CASE WHEN health_score < 40 THEN 1 END) as poor,
                COUNT(*) as total,
                ROUND(AVG(health_score)) as avg_score
            ")
            ->first();

        $worstClients = (clone $healthBase)
            ->where('health_score', '<', 40)
            ->orderBy('health_score')
            ->limit(10)
            ->get(['id', 'public_id', 'name', 'health_score', 'last_contact_at', 'total_revenue', 'company']);

        $bestClients = (clone $healthBase)
            ->where('health_score', '>=', 80)
            ->orderByDesc('health_score')
            ->limit(10)
            ->get(['id', 'public_id', 'name', 'health_score', 'last_contact_at', 'total_revenue', 'company']);

        $totalClients = Client::where('user_id', $userId)
            ->where('is_archived', false)
            ->count();

        $withoutScore = $totalClients - ($distribution->total ?? 0);

        // ==================== بيانات Filter Builder ====================
        $tags = ClientTag::query()
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->where('is_active', true)
            ->orderBy('priority')
            ->get(['id', 'name', 'color', 'icon']);

        $statuses = ClientStatus::cases();
        $sources  = ClientSource::cases();

        return view('crm.segments.index', compact(
            'segments',
            'distribution',
            'worstClients',
            'bestClients',
            'totalClients',
            'withoutScore',
            'tags',
            'statuses',
            'sources',
        ));
    }

    /**
     * حفظ شريحة جديدة
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        $request->validate([
            'name'    => ['required', 'string', 'max:80'],
            'filters' => ['required', 'array'],
            'pinned'  => ['sometimes', 'boolean'],
        ]);

        $segment = $this->segmentService->create(
            userId:  $request->user()->id,
            name:    $request->string('name')->toString(),
            filters: $request->input('filters', []),
            pinned:  $request->boolean('pinned', false),
        );

        return response()->json([
            'data'    => $segment,
            'message' => 'تم حفظ الشريحة.',
        ], 201);
    }

    /**
     * معاينة نتائج فلاتر (بدون حفظ)
     */
    public function preview(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $request->validate([
            'filters'  => ['required', 'array'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
        ]);

        $results = $this->segmentService->preview(
            userId:  $request->user()->id,
            filters: $request->input('filters', []),
            perPage: (int) $request->input('per_page', 20),
        );

        return response()->json(['data' => $results]);
    }

    /**
     * تنفيذ شريحة محفوظة وإرجاع نتائجها
     */
    public function execute(Request $request, SavedSegment $segment): JsonResponse
    {
        $this->authorize('view', $segment);

        $results = $this->segmentService->execute(
            segment: $segment,
            perPage: (int) $request->input('per_page', 20),
        );

        return response()->json([
            'data'    => $results,
            'segment' => $segment,
        ]);
    }

    /**
     * تثبيت / إلغاء تثبيت شريحة
     */
    public function pin(Request $request, SavedSegment $segment): JsonResponse
    {
        $this->authorize('update', $segment);

        $pinned  = $request->boolean('pinned', true);
        $segment = $this->segmentService->pin($segment, $pinned);

        return response()->json([
            'data'    => $segment,
            'message' => $pinned ? 'تم تثبيت الشريحة.' : 'تم إلغاء تثبيت الشريحة.',
        ]);
    }

    /**
     * إعادة حساب مؤشرات صحة العملاء للمستخدم الحالي
     */
    public function recalculateHealth(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $result = $this->healthService->recalculateForUser($request->user()->id);

        return response()->json([
            'message'   => "تم حساب مؤشر الصحة لـ {$result['processed']} عميل بنجاح.",
            'processed' => $result['processed'],
        ]);
    }

    /**
     * حذف شريحة محفوظة
     */
    public function destroy(Request $request, SavedSegment $segment): JsonResponse
    {
        $this->authorize('delete', $segment);

        $this->segmentService->destroy($segment);

        return response()->json(['message' => 'تم حذف الشريحة.']);
    }
}
