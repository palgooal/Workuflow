<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\Models\ClientFieldDefinition;
use App\Modules\CRM\Services\ClientCustomFieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientCustomFieldController extends Controller
{
    public function __construct(
        private readonly ClientCustomFieldService $fieldService,
    ) {}

    /**
     * قائمة تعريفات الحقول المخصصة للمستخدم
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manageCustomFields', Client::class);

        $fields = $this->fieldService->forUser($request->user()->id);

        return response()->json(['data' => $fields]);
    }

    /**
     * إنشاء حقل مخصص جديد
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('manageCustomFields', Client::class);

        $data = $request->validate([
            'label'       => ['required', 'string', 'max:80'],
            'key'         => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'],
            'type'        => ['required', 'string', 'in:text,number,date,boolean,select,multi_select,url,email,phone'],
            'options'     => ['nullable', 'array'],
            'options.*'   => ['string', 'max:100'],
            'is_required' => ['sometimes', 'boolean'],
            'is_visible'  => ['sometimes', 'boolean'],
        ]);

        $field = $this->fieldService->create($request->user()->id, $data);

        return response()->json([
            'data'    => $field,
            'message' => 'تم إنشاء الحقل المخصص.',
        ], 201);
    }

    /**
     * تعديل حقل مخصص
     */
    public function update(Request $request, ClientFieldDefinition $field): JsonResponse
    {
        $this->authorize('manageCustomFields', Client::class);

        // التحقق أن الحقل يخص المستخدم
        if ($field->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'label'       => ['sometimes', 'string', 'max:80'],
            'type'        => ['sometimes', 'string', 'in:text,number,date,boolean,select,multi_select,url,email,phone'],
            'options'     => ['nullable', 'array'],
            'options.*'   => ['string', 'max:100'],
            'is_required' => ['sometimes', 'boolean'],
            'is_visible'  => ['sometimes', 'boolean'],
        ]);

        $field = $this->fieldService->update($field, $data);

        return response()->json([
            'data'    => $field,
            'message' => 'تم تحديث الحقل.',
        ]);
    }

    /**
     * حذف حقل مخصص وجميع قيمه
     */
    public function destroy(Request $request, ClientFieldDefinition $field): JsonResponse
    {
        $this->authorize('manageCustomFields', Client::class);

        if ($field->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->fieldService->destroy($field);

        return response()->json(['message' => 'تم حذف الحقل وجميع بياناته.']);
    }

    /**
     * إعادة ترتيب الحقول
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manageCustomFields', Client::class);

        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $this->fieldService->reorder($request->user()->id, $request->input('ids'));

        return response()->json(['message' => 'تم تحديث الترتيب.']);
    }

    /**
     * حفظ قيمة حقل مخصص لعميل
     */
    public function saveValue(Request $request, string $clientPublicId, ClientFieldDefinition $field): JsonResponse
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('update', $client);

        if ($field->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'value' => ['required'],
        ]);

        $fieldValue = $this->fieldService->saveValue($client, $field, $request->input('value'));

        return response()->json([
            'data'    => $fieldValue,
            'message' => 'تم حفظ القيمة.',
        ]);
    }

    // ==================== Helper ====================

    private function resolveClient(string $publicId, int $userId): Client
    {
        return Client::where('public_id', $publicId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
