<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\DTOs\BulkTagDTO;
use App\Modules\CRM\DTOs\CreateTagDTO;
use App\Modules\CRM\Models\ClientTag;
use App\Modules\CRM\Requests\BulkTagRequest;
use App\Modules\CRM\Requests\StoreTagRequest;
use App\Modules\CRM\Services\ClientTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientTagController extends Controller
{
    public function __construct(
        private readonly ClientTagService $tagService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $userId = $request->user()->id;

        // AJAX/API — إعادة JSON
        if ($request->wantsJson()) {
            $tags = $this->tagService->forUser($userId);
            return response()->json(['data' => $tags]);
        }

        // Web — عرض صفحة إدارة الوسوم
        $tags = ClientTag::query()
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->where('is_active', true)
            ->withCount('clients')
            ->orderBy('priority')
            ->get();

        $systemTags = $tags->filter(fn ($t) => $t->isSystem());
        $customTags = $tags->filter(fn ($t) => ! $t->isSystem());

        return view('crm.tags.index', compact('systemTags', 'customTags'));
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->create(CreateTagDTO::fromRequest($request));

        return response()->json(['data' => $tag, 'message' => 'تم إنشاء الوسم.'], 201);
    }

    public function update(Request $request, ClientTag $tag): JsonResponse
    {
        $this->authorize('update', $tag);

        $tag = $this->tagService->update($tag, $request->only(['name', 'color', 'icon']));

        return response()->json(['data' => $tag, 'message' => 'تم تحديث الوسم.']);
    }

    public function destroy(ClientTag $tag): JsonResponse
    {
        $this->authorize('delete', $tag);

        $this->tagService->delete($tag);

        return response()->json(['message' => 'تم حذف الوسم.']);
    }

    public function bulkAssign(BulkTagRequest $request): JsonResponse
    {
        $results = $this->tagService->bulk(BulkTagDTO::fromRequest($request));

        return response()->json([
            'message' => "تمت المعالجة: {$results['processed']} عميل.",
            'data'    => $results,
        ]);
    }

    public function assign(Request $request, Client $client, ClientTag $tag): JsonResponse
    {
        $this->authorize('update', $client);

        $this->tagService->assign($client, $tag, $request->user()->id);

        return response()->json(['message' => "تم تعيين الوسم {$tag->name}."]);
    }

    public function remove(Request $request, Client $client, ClientTag $tag): JsonResponse
    {
        $this->authorize('update', $client);

        $this->tagService->remove($client, $tag, $request->user()->id);

        return response()->json(['message' => "تمت إزالة الوسم {$tag->name}."]);
    }

    public function suggest(Request $request, string $publicId): JsonResponse
    {
        $client = Client::where('public_id', $publicId)
            ->where('user_id', $request->user()->id)
            ->with('tags')
            ->firstOrFail();

        $this->authorize('view', $client);

        $suggestions = $this->tagService->suggest($client);

        return response()->json(['data' => $suggestions]);
    }

    /**
     * إعادة ترتيب الوسوم — PATCH /clients/tags/reorder
     * يستقبل: {order: [id1, id2, ...]}
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => ['required', 'array']]);

        $userId = $request->user()->id;
        $ids    = $request->input('order');

        foreach ($ids as $priority => $id) {
            ClientTag::where('id', $id)
                ->where('user_id', $userId) // حماية: فقط وسوم المستخدم
                ->update(['priority' => $priority + 1]);
        }

        \Illuminate\Support\Facades\Cache::forget("crm:tags:user:{$userId}");

        return response()->json(['message' => 'تم حفظ الترتيب.']);
    }
}
