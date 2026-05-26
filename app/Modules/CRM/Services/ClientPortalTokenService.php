<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Models\ClientPortalToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ClientPortalTokenService
 *
 * ⚠️ أمان حرج (C-04 Fix):
 * - generateSecureToken() يعيد النص الأصلي مرة واحدة للعرض
 * - يُخزِّن hash('sha256', $plaintext) في قاعدة البيانات
 * - القيمة الأصلية لا تُخزَّن أبداً بعد العرض
 */
class ClientPortalTokenService
{
    private const DEFAULT_TTL_DAYS = 30;

    // ==================== إنشاء رمز ====================

    /**
     * إنشاء رمز بوابة جديد.
     * يُعيد نموذج ClientPortalToken مع خاصية $plaintext مؤقتة للعرض الفوري.
     */
    public function create(Client $client, array $data, int $createdBy): ClientPortalToken
    {
        return DB::transaction(function () use ($client, $data, $createdBy) {

            // 1. توليد رمز عشوائي آمن (64 حرف = 384 bit entropy)
            $plaintext = $this->generatePlaintextToken();

            // 2. تخزين hash فقط — لا القيمة الأصلية
            $tokenHash = hash('sha256', $plaintext);

            $token = ClientPortalToken::create([
                'client_id'   => $client->id,
                'token'       => $tokenHash,
                'permissions' => $data['permissions'] ?? [],
                'expires_at'  => now()->addDays($data['ttl_days'] ?? self::DEFAULT_TTL_DAYS),
                'created_by'  => $createdBy,
            ]);

            // 3. إرفاق النص الأصلي كخاصية مؤقتة للعرض الفوري (لا تُحفظ)
            $token->plaintext_token = $plaintext;

            return $token;
        });
    }

    /**
     * إبطال (حذف) رمز بوابة
     */
    public function revoke(ClientPortalToken $token): void
    {
        $token->delete();
    }

    // ==================== Queries ====================

    public function forClient(Client $client): Collection
    {
        return ClientPortalToken::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * البحث عن رمز بالنص الأصلي (للمصادقة)
     */
    public function findByPlaintext(string $plaintext): ?ClientPortalToken
    {
        $hash = hash('sha256', $plaintext);

        return ClientPortalToken::where('token', $hash)
            ->where('expires_at', '>', now())
            ->with('client')
            ->first();
    }

    // ==================== Helper ====================

    /**
     * توليد رمز فريد (نص أصلي) — التحقق من عدم التكرار بمقارنة الـ hash
     */
    private function generatePlaintextToken(): string
    {
        do {
            $plaintext = Str::random(64);
            $hash      = hash('sha256', $plaintext);
        } while (ClientPortalToken::where('token', $hash)->exists());

        return $plaintext;
    }
}
