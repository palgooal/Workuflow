<?php

namespace App\Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\CRM\Enums\PortalPermission;
use App\Modules\CRM\Models\ClientPortalToken;
use App\Modules\CRM\Services\ClientPortalTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalTokenController extends Controller
{
    public function __construct(
        private readonly ClientPortalTokenService $tokenService,
    ) {}

    /**
     * صفحة إدارة رموز البوابة للعميل (S8.3)
     */
    public function index(Request $request, string $clientPublicId): View|JsonResponse
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('managePortal', $client);

        if ($request->wantsJson()) {
            $tokens = $this->tokenService->forClient($client);
            return response()->json(['data' => $tokens]);
        }

        $tokens      = $this->tokenService->forClient($client);
        $permissions = PortalPermission::cases();

        return view('crm.portal-tokens.index', compact('client', 'tokens', 'permissions'));
    }

    /**
     * إنشاء رمز بوابة جديد
     * ⚠️ يعيد plaintext_token مرة واحدة — لا يُمكن استرجاعه لاحقاً
     */
    public function store(Request $request, string $clientPublicId): JsonResponse
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('managePortal', $client);

        $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'in:' . implode(',', PortalPermission::values())],
            'ttl_days'    => ['sometimes', 'integer', 'min:1', 'max:365'],
        ]);

        $token = $this->tokenService->create(
            client:    $client,
            data:      [
                'permissions' => $request->input('permissions'),
                'ttl_days'    => (int) $request->input('ttl_days', 30),
            ],
            createdBy: $request->user()->id,
        );

        return response()->json([
            'data'           => $token,
            'plaintext_token' => $token->plaintext_token,  // ← يُعرض مرة واحدة فقط
            'portal_url'     => route('portal.auth') . '?token=' . $token->plaintext_token,
            'message'        => 'تم إنشاء رمز البوابة. احفظ الرمز الآن — لن يظهر مجدداً.',
        ], 201);
    }

    /**
     * إبطال رمز بوابة
     */
    public function destroy(Request $request, string $clientPublicId, ClientPortalToken $token): JsonResponse
    {
        $client = $this->resolveClient($clientPublicId, $request->user()->id);
        $this->authorize('managePortal', $client);

        if ($token->client_id !== $client->id) {
            abort(404);
        }

        $this->tokenService->revoke($token);

        return response()->json(['message' => 'تم إبطال رمز البوابة.']);
    }

    // ==================== Helper ====================

    private function resolveClient(string $publicId, int $userId): Client
    {
        return Client::where('public_id', $publicId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
