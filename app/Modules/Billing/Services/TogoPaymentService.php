<?php

namespace App\Modules\Billing\Services;

use App\Models\User;
use App\Modules\Billing\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TogoPaymentService — بوابة دفع Togo (togo.ps)
 *
 * الخطوات:
 *   1. إنشاء receiver address (مرة واحدة عبر: php artisan togo:setup-receiver)
 *   2. إنشاء RFP order → createCheckoutUrl()
 *   3. إعادة توجيه المستخدم لـ Togo → يتولاها BillingController
 *   4. التحقق من الدفع عند الـ callback → verifyOrder()
 */
class TogoPaymentService implements PaymentProviderInterface
{
    private const BASE_URL = 'https://api.togo.ps';

    private string $apiKey;
    private string $receiverAddressId;
    private string $currency;

    public function __construct()
    {
        $this->apiKey            = config('billing.togo.api_key', '');
        $this->receiverAddressId = config('billing.togo.receiver_address_id', '');
        $this->currency          = config('billing.togo.currency', 'ILS');
    }

    // ──────────────────────────────────────────────────────────
    // PaymentProviderInterface
    // ──────────────────────────────────────────────────────────

    /**
     * الخطوة 2+3: إنشاء RFP order وإرجاع رابط صفحة الدفع.
     *
     * يُخزّن order_id في session للتحقق لاحقاً عبر verifyOrder().
     *
     * @throws \RuntimeException إذا فشل API
     */
    public function createCheckoutUrl(User $user, string $plan): string
    {
        $this->assertConfigured();

        $price = $this->getPlanPrice($plan);

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post(self::BASE_URL . '/api/v1/actions', [
                'event' => 'Create_Visa',
                'data'  => [
                    'type'                        => 'RFP',
                    'value'                       => $price,
                    'receiver_address_id'         => $this->receiverAddressId,
                    'receiver_email'              => $user->email,
                    'currency'                    => $this->currency,
                    'source'                      => 'external_website',
                    'prevent_sms_link'            => false,
                    'payment_success_redirect_link' => route('billing.togo.callback'),
                    'payment_cancel_redirect_link'  => route('billing.togo.cancel'),
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Togo createCheckoutUrl failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'user'   => $user->id,
                'plan'   => $plan,
            ]);
            throw new \RuntimeException('فشل الاتصال ببوابة Togo. حاول مجدداً.');
        }

        $data = $response->json('data');

        if (empty($data['hashed_id']) || empty($data['id'])) {
            Log::error('Togo: بيانات order ناقصة', ['response' => $response->json()]);
            throw new \RuntimeException('استجابة Togo غير مكتملة.');
        }

        // حفظ الـ order_id (وليس hashed_id) في session للتحقق لاحقاً
        session([
            'togo_order_id'   => $data['id'],
            'togo_order_plan' => $plan,
        ]);

        // الخطوة 3: رابط صفحة الدفع
        return self::BASE_URL
            . '/api/v1/direct-pay'
            . '?orderId=' . urlencode($data['hashed_id'])
            . '&receiverEmail=' . urlencode($user->email);
    }

    /**
     * Togo لا تملك صفحة إدارة اشتراك — نُعيد صفحة الفواتير.
     */
    public function createPortalUrl(User $user): string
    {
        return route('billing.index');
    }

    /**
     * Togo لا تدعم Webhooks — تستخدم redirect callbacks.
     * لا تستدعِ هذه الطريقة؛ استخدم togoCallback() في BillingController بدلاً منها.
     */
    public function parseWebhook(string $payload, string $signature): array
    {
        throw new \LogicException(
            'Togo لا تدعم Webhooks. استخدم callback URL بدلاً منه.'
        );
    }

    // ──────────────────────────────────────────────────────────
    // Togo-specific methods
    // ──────────────────────────────────────────────────────────

    /**
     * الخطوة 4: التحقق من حالة الطلب.
     *
     * @return array{status: string, ...} بيانات الطلب من Togo
     * @throws \RuntimeException إذا فشل API
     */
    public function verifyOrder(string $orderId): array
    {
        $this->assertConfigured();

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(10)
            ->get(self::BASE_URL . '/api/v1/orders', ['id' => $orderId]);

        if (! $response->successful()) {
            Log::error('Togo verifyOrder failed', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('فشل التحقق من حالة الدفع.');
        }

        return $response->json('data', []);
    }

    /**
     * الخطوة 1: إنشاء receiver address (يُستدعى من Artisan command مرة واحدة).
     *
     * @return array بيانات العنوان المُنشأ (يحتوي على الـ id)
     * @throws \RuntimeException إذا فشل API
     */
    public function createReceiverAddress(
        string $name,
        string $phone,
        string $countryCode,
        string $countryName,
        string $city,
        string $details = '',
        bool $phoneConnectedToWhatsapp = false,
    ): array {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('TOGO_API_KEY غير مضبوط في .env');
        }

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post(self::BASE_URL . '/api/v1/receivers-addresses', [
                'receiver_name'            => $name,
                'receiver_phone_number'    => $phone,
                'country_code'             => $countryCode,
                'country_name'             => $countryName,
                'phone_connected_to_whats' => $phoneConnectedToWhatsapp,
                'city'                     => $city,
                'details'                  => $details,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'فشل إنشاء receiver address: ' . $response->body()
            );
        }

        $data = $response->json('data');

        if (empty($data['id'])) {
            throw new \RuntimeException('لم يُرجع Togo الـ ID للعنوان المُنشأ.');
        }

        return $data;
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    private function assertConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('TOGO_API_KEY غير مضبوط في .env');
        }
        if (empty($this->receiverAddressId)) {
            throw new \RuntimeException(
                'TOGO_RECEIVER_ADDRESS_ID غير مضبوط. شغّل: php artisan togo:setup-receiver'
            );
        }
    }

    private function getPlanPrice(string $plan): float
    {
        $plans = config('billing.plans', []);
        $price = (float) ($plans[$plan]['price'] ?? 0);

        if ($price <= 0) {
            throw new \RuntimeException("سعر الخطة [{$plan}] غير مضبوط في billing config.");
        }

        return $price;
    }
}
