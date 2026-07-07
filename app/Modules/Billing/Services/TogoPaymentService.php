<?php

namespace App\Modules\Billing\Services;

use App\Models\Client;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Modules\Billing\Contracts\PaymentProviderInterface;
use App\Support\Helpers\Country;
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
    private const URL_LIVE    = 'https://api.togo.ps';
    private const URL_SANDBOX = 'https://api.dev.togo.ps';

    private string $apiKey;
    private string $receiverAddressId;
    private string $currency;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey            = config('billing.togo.api_key', '');
        $this->receiverAddressId = config('billing.togo.receiver_address_id', '');
        $this->currency          = config('billing.togo.currency', 'ILS');
        $this->baseUrl           = config('billing.togo.mode', 'sandbox') === 'live'
            ? self::URL_LIVE
            : self::URL_SANDBOX;
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

        // receiver_address خاص بالمشترك نفسه (اسمه/عنوانه) لو بياناته مكتملة
        // — بدل receiver_address الثابت للمنصة دائماً. راجع
        // docs/PAYMENT-COLLECTION.md — "receiver_address لكل عميل/مشترك".
        // بخلاف الفواتير (حيث نمنع الدفع كلياً لو بيانات العميل ناقصة)، هنا
        // نتساهل عمداً: لو المشترك لسه ما عبّى عنوان الفوترة في الإعدادات
        // (users.billing_address/billing_city)، نكمل الدفع بالعنوان الثابت
        // للمنصة بدل منعه من الاشتراك. البانر في settings.index يشجعه يكمل
        // بياناته لاحقاً حتى تظهر عملياته باسمه الخاص عند Togo.
        $receiverAddressId = $this->receiverAddressId;
        if ($user->isReadyForElectronicPayment()) {
            try {
                $receiverAddressId = $this->getOrCreateReceiverAddressForUser($user);
            } catch (\Throwable $e) {
                Log::warning('Togo: فشل الحصول على receiver_address خاص بالمشترك — استخدام العنوان الثابت', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post($this->baseUrl . '/api/v1/actions', [
                'event' => 'Create_Visa',
                'data'  => [
                    'type'                          => 'RFP',
                    'value'                         => $price,
                    'receiver_address_id'           => $receiverAddressId,
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
        $checkoutUrl = $this->baseUrl
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

        // تسجيل أول حدث في الـ timeline
        $order->addTimelineEvent('order.created', [
            'plan'     => $plan,
            'cycle'    => $cycle,
            'amount'   => $price,
            'currency' => $currency,
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
     * إنشاء RFP order لتحصيل فاتورة (مبلغ حر، ليس خطة اشتراك) نيابة عن مشترك.
     *
     * تُستخدم من InvoicePaymentController — الأموال تُحصَّل في حساب دراهم
     * على Togo، ثم تُسوَّى مع المشترك يدوياً لاحقاً (PaymentCollection.status).
     *
     * @param  float   $amount             المبلغ المطلوب تحصيله
     * @param  string  $currency           عملة الفاتورة
     * @param  string  $receiverEmail      بريد العميل الدافع
     * @param  string  $successUrl         رابط العودة عند نجاح الدفع
     * @param  string  $cancelUrl          رابط العودة عند إلغاء الدفع
     * @param  ?string $receiverAddressId  receiver_address خاص بالعميل الدافع (من
     *         getOrCreateReceiverAddressForClient) — لو null يُستخدَم العنوان الثابت
     *         للمنصة (fallback قديم، غير مستحسن الاعتماد عليه بعد الآن).
     * @return array{checkout_url: string, provider_order_id: string, provider_hashed_id: string, raw: array}
     * @throws \RuntimeException إذا فشل API
     */
    public function createInvoicePaymentOrder(
        float $amount,
        string $currency,
        string $receiverEmail,
        string $successUrl,
        string $cancelUrl,
        ?string $receiverAddressId = null,
    ): array {
        $this->assertConfigured();

        $data = [
            'type'                          => 'RFP',
            'value'                         => $amount,
            'receiver_address_id'           => $receiverAddressId ?: $this->receiverAddressId,
            'receiver_email'                => $receiverEmail,
            'currency'                      => $currency,
            'source'                        => 'external_website',
            'prevent_sms_link'              => false,
            'payment_success_redirect_link' => $successUrl,
            'payment_cancel_redirect_link'  => $cancelUrl,
        ];

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post($this->baseUrl . '/api/v1/actions', [
                'event' => 'Create_Visa',
                'data'  => $data,
            ]);

        if (! $response->successful()) {
            Log::error('Togo createInvoicePaymentOrder failed', [
                'status'   => $response->status(),
                'body'     => $response->body(),
                'amount'   => $amount,
                'currency' => $currency,
            ]);
            throw new \RuntimeException('فشل الاتصال ببوابة Togo. حاول مجدداً.');
        }

        $data = $response->json('data');

        if (empty($data['hashed_id']) || empty($data['id'])) {
            Log::error('Togo: بيانات order ناقصة (invoice payment)', ['response' => $response->json()]);
            throw new \RuntimeException('استجابة Togo غير مكتملة.');
        }

        $checkoutUrl = $this->baseUrl
            . '/api/v1/direct-pay'
            . '?orderId=' . urlencode($data['hashed_id'])
            . '&receiverEmail=' . urlencode($receiverEmail);

        return [
            'checkout_url'       => $checkoutUrl,
            'provider_order_id'  => $data['id'],
            'provider_hashed_id' => $data['hashed_id'],
            'raw'                => $data,
        ];
    }

    /**
     * العملات التي تدعمها بوابة Togo فعلياً للدفع الإلكتروني لفواتير المستقلين
     * (createInvoicePaymentOrder). عملة الفاتورة نفسها (invoice.currency) يمكن
     * أن تكون أي عملة من القائمة الكاملة في App\Support\Helpers\Currency — هذا
     * القيد خاص فقط بمسار الدفع عبر Togo، وليس بإنشاء الفاتورة أو عرضها.
     *
     * ⚠️ لا تُستخدم هذه القائمة لأي تحويل عملة تلقائي ("الدفع بما يعادلها
     * بالشيكل" غير مُنفَّذ بعد لغياب سعر صرف رسمي) — فقط لمنع فتح checkout
     * لعملة لا تدعمها Togo أصلاً. راجع docs/PAYMENT-COLLECTION.md.
     *
     * @return string[]
     */
    public function supportedInvoiceCurrencies(): array
    {
        return ['ILS', 'USD'];
    }

    /**
     * هل عملة الفاتورة مدعومة للدفع الإلكتروني عبر Togo؟ فحص غير حسّاس لحالة
     * الأحرف (مثلاً "ils" أو "ILS" كلاهما مدعوم).
     */
    public function isInvoiceCurrencySupported(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->supportedInvoiceCurrencies(), true);
    }

    /**
     * يحاول استخراج مبلغ عمولة Togo من بيانات الطلب المُرجَعة عند verifyOrder().
     *
     * ⚠️ Togo لا تُوثِّق حقلاً رسمياً ثابتاً لعمولة الـ RFP حالياً، لذا نتحقق
     * من عدة أسماء حقول محتملة بأمان (بدون افتراض بنية غير موجودة). تُستخدم
     * من InvoicePaymentController@callback؛ إن لم يوجد أي حقل مطابق تُعاد
     * null ليعتمد المستدعي على إعدادات العمولة من لوحة الإدارة (Filament →
     * بوابة الدفع → عمولة تحصيل الفواتير، جدول settings group=payment).
     */
    public function extractCommissionAmount(array $orderData): ?float
    {
        foreach (['commission_amount', 'commission', 'fee_amount', 'fee', 'platform_fee'] as $key) {
            if (isset($orderData[$key]) && is_numeric($orderData[$key])) {
                return (float) $orderData[$key];
            }
        }

        // بعض الردود المحتملة قد تُغلِّف العمولة داخل fees{} أو breakdown{}
        if (isset($orderData['fees']) && is_array($orderData['fees'])) {
            foreach (['commission', 'platform', 'gateway'] as $nestedKey) {
                if (isset($orderData['fees'][$nestedKey]) && is_numeric($orderData['fees'][$nestedKey])) {
                    return (float) $orderData['fees'][$nestedKey];
                }
            }
        }

        return null;
    }

    /**
     * يحاول استخراج مبلغ التسوية بالشيكل (ILS) من بيانات الطلب المُرجَعة عند
     * verifyOrder() — Togo تُحصِّل وتُسوِّي فعلياً بالشيكل دائماً بغض النظر عن
     * عملة الفاتورة الأصلية. مثل extractCommissionAmount()، هذه محاولة "قدر
     * المستطاع" لعدة أسماء حقول محتملة؛ تُعيد null إن لم يوجد أي حقل مطابق
     * ليعتمد المستدعي (InvoicePaymentController@callback) على سعر صرف مُرجَع
     * (extractExchangeRate) أو على المراجعة اليدوية من الأدمن.
     */
    public function extractSettlementAmount(array $orderData): ?float
    {
        foreach (['settlement_amount', 'settled_amount', 'ils_amount', 'amount_ils', 'converted_amount', 'local_amount'] as $key) {
            if (isset($orderData[$key]) && is_numeric($orderData[$key])) {
                return (float) $orderData[$key];
            }
        }

        if (isset($orderData['settlement']) && is_array($orderData['settlement'])) {
            foreach (['amount', 'ils_amount', 'value'] as $nestedKey) {
                if (isset($orderData['settlement'][$nestedKey]) && is_numeric($orderData['settlement'][$nestedKey])) {
                    return (float) $orderData['settlement'][$nestedKey];
                }
            }
        }

        return null;
    }

    /**
     * يحاول استخراج سعر الصرف المُستخدَم لتحويل مبلغ الفاتورة إلى الشيكل من
     * بيانات الطلب المُرجَعة عند verifyOrder(). راجع extractSettlementAmount().
     */
    public function extractExchangeRate(array $orderData): ?float
    {
        foreach (['exchange_rate', 'fx_rate', 'conversion_rate', 'rate'] as $key) {
            if (isset($orderData[$key]) && is_numeric($orderData[$key])) {
                return (float) $orderData[$key];
            }
        }

        return null;
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
            ->get($this->baseUrl . '/api/v1/orders', ['id' => $orderId]);

        if (! $response->successful()) {
            Log::error('Togo verifyOrder failed', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('فشل التحقق من حالة الدفع.');
        }

        $json = $response->json();

        // Live API:    {"data": {"status": "PAID", ...}}
        // Sandbox API: {"items": [{...}], "totalItems": N}
        if (isset($json['data']) && is_array($json['data'])) {
            $data = $json['data'];
        } elseif (isset($json['items'][0]) && is_array($json['items'][0])) {
            $data = $json['items'][0];
        } else {
            $data = [];
        }

        Log::info('Togo verifyOrder parsed', [
            'order_id' => $orderId,
            'status'   => $data['status'] ?? 'NOT_FOUND',
            'data'     => $data,
        ]);

        return $data;
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

        // ── تنظيف رقم الهاتف قبل الإرسال ──────────────────────────────────
        // Togo يرفض الطلب كاملاً (500 — "Phone number must be valid national
        // or international number") لو الرقم فيه مسافات/أقواس/شرطات، حتى لو
        // كان صحيحاً منطقياً — مثال حقيقي وُوجِه فعلاً: "+1 (597) 601-4765"
        // (بيانات Faker تجريبية بحقل phone حر بلا قيود تنسيق في نموذج العميل).
        // نُبقي فقط '+' البادئة والأرقام، بنفس منطق تنظيف رقم واتساب الموجود
        // مسبقاً في invoices/show.blade.php.
        $normalizedPhone = self::normalizePhone($phone);

        if ($normalizedPhone === '' || strlen(ltrim($normalizedPhone, '+')) < 7) {
            throw new \RuntimeException(
                "رقم الهاتف \"{$phone}\" غير صالح لإنشاء عنوان دفع عند Togo — تأكد أنه رقم دولي كامل (مثال: +970599123456)."
            );
        }

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->timeout(15)
            ->post($this->baseUrl . '/api/v1/receivers-addresses', [
                'receiver_name'            => $name,
                'receiver_phone_number'    => $normalizedPhone,
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

    /**
     * receiver_address خاص بعميل مُعيَّن (get-or-create) — بدل استخدام
     * receiver_address ثابت للمنصة في كل عملية دفع، ما كان يُظهر نفس
     * الاسم/الهاتف/العنوان لكل الفواتير ("نفس النمط متكرر" — راجع
     * docs/PAYMENT-COLLECTION.md وتحذير Togo الرسمي في ملف الـ API عن
     * البيانات المضلِّلة/الناقصة).
     *
     * لو العميل عنده togo_receiver_address_id مخزَّن مسبقاً يُعاد استخدامه
     * مباشرة بدون أي استدعاء API. أول استدعاء فقط ينشئ السجل عند Togo
     * ويُخزِّن الـ id على $client دائماً.
     *
     * @throws \RuntimeException لو بيانات العميل ناقصة — تحقّق دائماً من
     *         Client::isReadyForElectronicPayment() قبل استدعاء هذه الدالة
     *         لتعرض رسالة واضحة بدل استثناء عام.
     */
    public function getOrCreateReceiverAddressForClient(Client $client): string
    {
        if (! empty($client->togo_receiver_address_id)) {
            return $client->togo_receiver_address_id;
        }

        $name = $client->paymentReceiverName();

        if ($name === null || empty($client->phone) || empty($client->city) || empty($client->address)) {
            throw new \RuntimeException('بيانات العميل غير مكتملة لإنشاء عنوان دفع عند Togo.');
        }

        $countryCode = $client->country ?: 'PS';

        $data = $this->createReceiverAddress(
            name:        $name,
            phone:       $client->phone,
            countryCode: $countryCode,
            countryName: Country::name($countryCode),
            city:        $client->city,
            details:     $client->address,
        );

        $client->update(['togo_receiver_address_id' => $data['id']]);

        Log::info('Togo: receiver_address created for client', [
            'client_id'           => $client->id,
            'receiver_address_id' => $data['id'],
        ]);

        return $data['id'];
    }

    /**
     * نفس فكرة getOrCreateReceiverAddressForClient() لكن لعنوان فوترة
     * المشترك نفسه (يُستخدَم عند دفع اشتراك الباقة Pro/Business).
     *
     * @throws \RuntimeException لو بيانات فوترة المشترك ناقصة — تحقّق دائماً
     *         من User::isReadyForElectronicPayment() أولاً.
     */
    public function getOrCreateReceiverAddressForUser(User $user): string
    {
        if (! empty($user->togo_receiver_address_id)) {
            return $user->togo_receiver_address_id;
        }

        $name = $user->paymentReceiverName();

        if ($name === null || empty($user->phone) || empty($user->billing_city) || empty($user->billing_address)) {
            throw new \RuntimeException('بيانات فوترة المشترك غير مكتملة لإنشاء عنوان دفع عند Togo.');
        }

        $countryCode = $user->billing_country ?: 'PS';

        $data = $this->createReceiverAddress(
            name:        $name,
            phone:       $user->phone,
            countryCode: $countryCode,
            countryName: Country::name($countryCode),
            city:        $user->billing_city,
            details:     $user->billing_address,
        );

        $user->update(['togo_receiver_address_id' => $data['id']]);

        Log::info('Togo: receiver_address created for user', [
            'user_id'             => $user->id,
            'receiver_address_id' => $data['id'],
        ]);

        return $data['id'];
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

    /**
     * ينظّف رقم هاتف حر التنسيق (مسافات/أقواس/شرطات) لصيغة يقبلها Togo —
     * '+' بادئة اختيارية متبوعة بأرقام فقط. راجع createReceiverAddress()
     * أعلاه لسبب وجود هذه الخطوة (حادثة حقيقية: "+1 (597) 601-4765" مرفوض).
     */
    private static function normalizePhone(string $phone): string
    {
        $hasPlus = str_starts_with(trim($phone), '+');
        $digits  = preg_replace('/\D/', '', $phone) ?? '';

        return $digits === '' ? '' : ($hasPlus ? '+' . $digits : $digits);
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
