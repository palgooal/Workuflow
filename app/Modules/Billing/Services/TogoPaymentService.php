<?php

namespace App\Modules\Billing\Services;

use App\Models\PaymentOrder;
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
     * ينشئ سجل PaymentOrder في DB ويحفظ ULID المحلي فقط في session.
     * هذا يضمن استمرارية الدفع حتى لو انتهت الجلسة قبل الـ callback.
     *
     * @throws \RuntimeException إذا فشل API
     */
    public function createCheckoutUrl(User $user, string $plan, string $cycle = 'monthly'): string
    {
        $this->assertConfigured();

        $price    = $this->getPlanPrice($plan, $cycle);
        $currency = $this->currency;

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post(self::BASE_URL . '/api/v1/actions', [
                'event' => 'Create_Visa',
                'data'  => [
                    'type'                          => 'RFP',
                    'value'                         => $price,
                    'receiver_address_id'           => $this->receiverAddressId,
                    'receiver_email'                => $user->email,
                    'currency'                      => $currency,
                    'source'                        => 'external_website',
                    'prevent_sms_link'              => false,
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

        // بناء رابط صفحة الدفع مسبقاً — يُخزَّن في metadata لاستخدامه في صفحة التأكيد
        $checkoutUrl = self::BASE_URL
            . '/api/v1/direct-pay'
            . '?orderId=' . urlencode($data['hashed_id'])
            . '&receiverEmail=' . urlencode($user->email);

        // إنشاء سجل PaymentOrder في DB — يُستخدم لاحقاً في الـ callback
        $order = PaymentOrder::create([
            'user_id'            => $user->id,
            'plan'               => $plan,
            'cycle'              => $cycle,
            'provider'           => 'togo',
            'provider_order_id'  => $data['id'],
            'provider_hashed_id' => $data['hashed_id'] ?? null,
            'amount'             => $price,   // المبلغ الكامل المُحصّل (annual = 12 × monthly_equiv)
            'currency'           => $currency,
            'status'             => 'pending',
            'metadata'           => array_merge($data, [
                // حقول الفوترة — مفيدة للـ audit وعرض الفاتورة لاحقاً
                'billing_cycle'           => $cycle,
                'charged_months'          => $cycle === 'annual' ? 12 : 1,
                'displayed_monthly_price' => $this->getPlanMonthlyDisplayPrice($plan, $cycle),
                'plan'                    => $plan,
                // رابط الدفع — يُستخدم في صفحة تأكيد ما قبل الدفع
                'checkout_url'            => $checkoutUrl,
            ]),
        ]);

        // حفظ ULID المحلي فقط في session — ليس بيانات Togo كاملة
        session(['payment_order_id' => $order->id]);

        Log::info('Togo payment order created', [
            'order_id'          => $order->id,
            'provider_order_id' => $data['id'],
            'user'              => $user->id,
            'plan'              => $plan,
            'cycle'             => $cycle,
            'amount'            => $price,
            'currency'          => $currency,
        ]);

        // الخطوة 3: رابط صفحة الدفع
        return $checkoutUrl;
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

        // Togo API يقبل ASCII فقط — تحقق من كل الحقول النصية
        $this->assertAscii($name,        'الاسم الكامل (receiver_name)');
        $this->assertAscii($countryName, 'اسم الدولة (country_name)');
        $this->assertAscii($city,        'المدينة (city)');
        $this->assertAscii($details,     'التفاصيل (details)');

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

    /**
     * Togo API يقبل ASCII فقط (قيم ≤ 255).
     * يرمي استثناءً واضحاً إذا وُجد حرف عربي أو غير ASCII.
     */
    private function assertAscii(string $value, string $fieldLabel): void
    {
        if ($value === '') return;

        for ($i = 0; $i < mb_strlen($value, 'UTF-8'); $i++) {
            $char = mb_substr($value, $i, 1, 'UTF-8');
            if (ord($char) > 127 || mb_ord($char) > 127) {
                throw new \RuntimeException(
                    "حقل [{$fieldLabel}] يحتوي على حروف غير مقبولة.\n"
                    . "Togo API يقبل الإنجليزية فقط (ASCII).\n"
                    . "الحرف المشكل: \"{$char}\""
                );
            }
        }
    }

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

    /**
     * حساب المبلغ الذي سيُحصّل من المستخدم عبر بوابة الدفع.
     *
     * config/billing.php يخزّن السعر الشهري المُعادل (Display Price):
     *   billing.plans.pro.annual.price = 13  ← يُعرض للمستخدم كـ "$13/شهر"
     *
     * لكن عند الدفع السنوي يُحصّل المبلغ كاملاً مقدماً:
     *   annual  → 13 × 12 = 156 USD  (Charge Amount)
     *   monthly → 17 USD              (Charge Amount)
     *
     * ⚠️  لا تخلط بين Display Price (config) و Charge Amount (هذه الدالة).
     */
    private function getPlanPrice(string $plan, string $cycle = 'monthly'): float
    {
        // config: سعر شهري مُعادل للعرض (Display Price)
        $monthlyEquiv = (float) (config("billing.plans.{$plan}.{$cycle}.price") ?? 0);

        if ($monthlyEquiv <= 0) {
            throw new \RuntimeException(
                "سعر الخطة [{$plan}] دورة [{$cycle}] غير مضبوط في config/billing.php. "
                . "المسار المتوقع: billing.plans.{$plan}.{$cycle}.price"
            );
        }

        // السنوي = 12 شهراً تُدفع مقدماً
        return $cycle === 'annual' ? round($monthlyEquiv * 12, 2) : $monthlyEquiv;
    }

    /**
     * السعر الشهري المُعادل للعرض في الـ UI — قيمة config مباشرة بدون ضرب.
     * مثال: pro/annual → 13.0  (يُعرض كـ "$13/شهر يُدفع سنوياً")
     */
    private function getPlanMonthlyDisplayPrice(string $plan, string $cycle = 'monthly'): float
    {
        return (float) (config("billing.plans.{$plan}.{$cycle}.price") ?? 0);
    }
}
