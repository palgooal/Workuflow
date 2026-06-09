# مركز واتساب الأعمال — WhatsApp Business Center

> وثيقة المواصفات الكاملة | دراهم SaaS Financial Platform  
> الإصدار: 1.0.0 | تاريخ الإنشاء: 8 يونيو 2026 | المرحلة: Phase 26 — Sprint 1

---

## 1. نظرة عامة (Executive Overview)

**مركز واتساب الأعمال** يُحوّل دراهم من منصة مالية إلى منصة **اتصالات أعمال متكاملة**، تُمكّن المستقل وصاحب العمل الصغير من التواصل مع عملائه مباشرةً من داخل النظام — فواتير، عروض أسعار، تذكيرات دفع، تحديثات مشاريع — كل ذلك موثَّق في CRM Timeline بالكامل.

### الأهداف الاستراتيجية

| الهدف | التأثير |
|-------|---------|
| تقليل وقت متابعة الفواتير | من 15 دقيقة/فاتورة → 30 ثانية |
| زيادة معدل تحصيل المستحقات | +35% بفضل التذكيرات الآلية |
| توثيق كامل للتواصل | كل رسالة في CRM Timeline |
| تمييز دراهم عن المنافسين | ميزة فريدة في السوق المحلي |
| رافع للترقية (Upgrade Trigger) | ميزة Pro/Business حصرية |

---

## 2. المتطلبات الوظيفية (Functional Requirements)

### 2.1 قنوات الإرسال

| الوظيفة | الوصف | الأولوية |
|---------|-------|----------|
| إرسال فاتورة عبر واتساب | رابط PDF + ملخص نصي | P0 |
| إرسال رابط عرض سعر عبر واتساب | الرابط العام للعرض | P0 |
| تذكير دفع | رسالة تذكير بموعد الاستحقاق | P0 |
| متابعة (Follow-up) | رسالة متابعة ما بعد الإرسال | P1 |
| تحديث مشروع | رسالة تقدّم المشروع | P1 |
| رسالة ترحيب (Onboarding) | رسالة ترحيب بعميل جديد | P1 |
| تسجيل في CRM Timeline | كل رسالة واتساب موثَّقة | P0 |
| تتبع حالة الرسالة | مُرسَلة / تم القراءة (API) | P2 |
| قوالب واتساب | قوالب قابلة للتخصيص | P1 |
| واتساب Business API | دعم مستقبلي | P3 |

### 2.2 طريقة الإرسال الحالية (Phase 1)

في المرحلة الأولى، يُستخدم **wa.me deep-link** الذي يفتح واتساب على الجهاز ويملأ الرسالة تلقائياً — لا يتطلب API ولا تكلفة إضافية.

```
https://wa.me/{phone}?text={encoded_message}
```

**مزايا هذا النهج:**
- لا تكلفة إضافية
- يعمل مع أي رقم هاتف
- يدعم الشركات التي لا تملك WhatsApp Business API
- المستخدم يرى الرسالة قبل الإرسال ويمكنه تعديلها

**قيود هذا النهج:**
- يتطلب فتح تطبيق واتساب يدوياً
- لا يمكن تتبع الإرسال / القراءة تلقائياً
- لا يمكن الإرسال التلقائي بدون تدخل بشري

### 2.3 المرحلة المستقبلية — WhatsApp Business API (Phase 2)

```
Provider Options:
├── Meta Business API (مباشر)
├── Twilio WhatsApp
├── Vonage (Nexmo)
└── Infobip
```

---

## 3. تصميم قاعدة البيانات (Database Design)

### 3.1 جدول القوالب — `whatsapp_templates`

```sql
CREATE TABLE whatsapp_templates (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,           -- اسم القالب (للاستخدام الداخلي)
    type        VARCHAR(50) NOT NULL,            -- WhatsAppTemplateType enum
    subject     VARCHAR(255) NULL,              -- عنوان اختياري
    body        TEXT NOT NULL,                   -- نص القالب مع متغيرات {variable}
    variables   JSON NULL,                       -- قائمة المتغيرات المدعومة
    is_default  TINYINT(1) DEFAULT 0,           -- قالب افتراضي لهذا النوع
    is_active   TINYINT(1) DEFAULT 1,
    sort_order  SMALLINT DEFAULT 0,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_user_default (user_id, is_default)
);
```

**متغيرات القوالب المدعومة:**

| المتغير | الوصف |
|---------|-------|
| `{client_name}` | اسم العميل |
| `{invoice_number}` | رقم الفاتورة |
| `{invoice_total}` | إجمالي الفاتورة مع العملة |
| `{invoice_due_date}` | تاريخ الاستحقاق |
| `{invoice_link}` | رابط بوابة الفاتورة (مستقبلاً) |
| `{quote_number}` | رقم عرض السعر |
| `{quote_total}` | إجمالي العرض |
| `{quote_link}` | رابط بوابة العرض العام |
| `{quote_valid_until}` | تاريخ انتهاء صلاحية العرض |
| `{project_name}` | اسم المشروع |
| `{project_update}` | تحديث المشروع |
| `{user_name}` | اسم المستخدم (المُرسِل) |
| `{user_business}` | اسم النشاط التجاري |
| `{days_overdue}` | عدد الأيام على الاستحقاق |
| `{amount_due}` | المبلغ المستحق |

### 3.2 جدول سجل الرسائل — `whatsapp_messages`

```sql
CREATE TABLE whatsapp_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid            CHAR(26) NOT NULL UNIQUE,    -- مفتاح المسار
    user_id         BIGINT UNSIGNED NOT NULL,
    client_id       BIGINT UNSIGNED NULL,        -- FK → clients
    template_id     BIGINT UNSIGNED NULL,        -- FK → whatsapp_templates
    
    -- الكيان المرتبط
    messageable_type VARCHAR(100) NULL,          -- 'App\Models\Invoice' | 'App\Models\Quote' | 'App\Models\Project'
    messageable_id   BIGINT UNSIGNED NULL,       -- ID الكيان
    
    -- بيانات الإرسال
    type            VARCHAR(50) NOT NULL,        -- WhatsAppMessageType enum
    phone           VARCHAR(30) NOT NULL,        -- رقم الهاتف المُرسَل إليه
    body            TEXT NOT NULL,               -- الرسالة بعد تعبئة المتغيرات
    
    -- الحالة
    status          VARCHAR(30) NOT NULL DEFAULT 'pending', -- WhatsAppMessageStatus enum
    sent_at         TIMESTAMP NULL,
    delivered_at    TIMESTAMP NULL,
    read_at         TIMESTAMP NULL,
    failed_at       TIMESTAMP NULL,
    failure_reason  VARCHAR(500) NULL,
    
    -- البيانات الإضافية
    metadata        JSON NULL,                   -- بيانات إضافية (API response, etc.)
    provider        VARCHAR(50) DEFAULT 'wame',  -- 'wame' | 'twilio' | 'meta'
    provider_msg_id VARCHAR(255) NULL,           -- ID الرسالة عند مزود API
    
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (template_id) REFERENCES whatsapp_templates(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, status),
    INDEX idx_user_type (user_id, type),
    INDEX idx_messageable (messageable_type, messageable_id),
    INDEX idx_client (client_id),
    INDEX idx_sent_at (sent_at)
);
```

### 3.3 جدول إعدادات واتساب — `whatsapp_settings`

```sql
CREATE TABLE whatsapp_settings (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL UNIQUE,
    
    -- الإعدادات العامة
    is_enabled              TINYINT(1) DEFAULT 1,
    default_country_code    VARCHAR(5) DEFAULT '+966',
    
    -- إعدادات التوقيع
    signature_enabled       TINYINT(1) DEFAULT 1,
    signature_text          VARCHAR(500) NULL,       -- نص التوقيع الافتراضي
    
    -- إعدادات التذكيرات التلقائية
    auto_reminder_enabled   TINYINT(1) DEFAULT 0,    -- Pro+ فقط
    reminder_days_before    TINYINT UNSIGNED DEFAULT 3, -- أيام قبل الاستحقاق
    reminder_days_after     TINYINT UNSIGNED DEFAULT 1, -- أيام بعد الاستحقاق
    
    -- إعدادات API (مستقبلي)
    api_provider            VARCHAR(50) NULL,        -- 'twilio' | 'meta' | 'vonage'
    api_key                 VARCHAR(500) NULL,       -- مشفَّرة
    api_secret              VARCHAR(500) NULL,       -- مشفَّرة
    api_phone_number        VARCHAR(30) NULL,        -- رقم الأعمال
    api_verified            TINYINT(1) DEFAULT 0,
    
    created_at              TIMESTAMP NULL,
    updated_at              TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 4. معمارية النظام (Architecture Design)

### 4.1 Enums

#### `WhatsAppTemplateType`
```php
// app/Modules/WhatsApp/Enums/WhatsAppTemplateType.php
enum WhatsAppTemplateType: string {
    case InvoiceSent        = 'invoice_sent';
    case InvoiceReminder    = 'invoice_reminder';
    case InvoiceOverdue     = 'invoice_overdue';
    case QuoteSent          = 'quote_sent';
    case QuoteFollowUp      = 'quote_follow_up';
    case ProjectUpdate      = 'project_update';
    case PaymentReceived    = 'payment_received';
    case ClientOnboarding   = 'client_onboarding';
    case Custom             = 'custom';

    public function label(): string { /* Arabic labels */ }
    public function defaultBody(): string { /* القالب الافتراضي */ }
    public function variables(): array { /* المتغيرات المتاحة */ }
}
```

#### `WhatsAppMessageStatus`
```php
enum WhatsAppMessageStatus: string {
    case Pending    = 'pending';    // جاهز للإرسال
    case Opened     = 'opened';     // فُتح deep-link
    case Sent       = 'sent';       // API: مُرسَل
    case Delivered  = 'delivered';  // API: وصل للجهاز
    case Read       = 'read';       // API: قُرئ
    case Failed     = 'failed';     // فشل
}
```

### 4.2 Models

#### `WhatsAppTemplate`
```php
// app/Modules/WhatsApp/Models/WhatsAppTemplate.php
class WhatsAppTemplate extends Model {
    use HasUlids, BelongsToUser;
    
    // العلاقات
    public function messages(): HasMany { }
    public function user(): BelongsTo { }
    
    // مساعدات
    public function fillVariables(array $data): string {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }
        return $body;
    }
}
```

#### `WhatsAppMessage`
```php
// app/Modules/WhatsApp/Models/WhatsAppMessage.php
class WhatsAppMessage extends Model {
    use HasUlids, BelongsToUser;
    
    // Polymorphic
    public function messageable(): MorphTo { }
    
    // العلاقات
    public function client(): BelongsTo { }
    public function template(): BelongsTo { }
    
    // مساعدات
    public function buildWameLink(): string {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        return 'https://wa.me/' . $phone . '?text=' . urlencode($this->body);
    }
    
    public function markAsOpened(): void {
        $this->update(['status' => WhatsAppMessageStatus::Opened, 'sent_at' => now()]);
    }
}
```

### 4.3 DTOs

```php
// app/Modules/WhatsApp/DTOs/SendWhatsAppDTO.php
readonly class SendWhatsAppDTO {
    public function __construct(
        public int $userId,
        public ?int $clientId,
        public WhatsAppTemplateType $type,
        public string $phone,
        public string $body,
        public ?string $messageableType = null,
        public ?int $messageableId = null,
        public ?int $templateId = null,
        public string $provider = 'wame',
    ) {}

    public static function forInvoice(Invoice $invoice, string $body): self {
        return new self(
            userId: $invoice->user_id,
            clientId: $invoice->client_id,
            type: WhatsAppTemplateType::InvoiceSent,
            phone: $invoice->client->phone ?? '',
            body: $body,
            messageableType: Invoice::class,
            messageableId: $invoice->id,
        );
    }

    public static function forQuote(Quote $quote, string $body): self {
        return new self(
            userId: $quote->user_id,
            clientId: $quote->client_id,
            type: WhatsAppTemplateType::QuoteSent,
            phone: $quote->client->phone ?? '',
            body: $body,
            messageableType: Quote::class,
            messageableId: $quote->id,
        );
    }
}
```

### 4.4 Actions

```php
// app/Modules/WhatsApp/Actions/PrepareWhatsAppMessageAction.php
class PrepareWhatsAppMessageAction {
    public function execute(
        WhatsAppTemplateType $type,
        Model $entity,
        ?int $templateId = null
    ): WhatsAppMessage {
        // 1. جلب القالب (مخصص أو افتراضي)
        $template = $this->resolveTemplate($type, $entity->user_id, $templateId);
        
        // 2. بناء بيانات المتغيرات
        $variables = $this->buildVariables($type, $entity);
        
        // 3. تعبئة القالب
        $body = $template->fillVariables($variables);
        
        // 4. إنشاء سجل الرسالة
        return WhatsAppMessage::create(
            SendWhatsAppDTO::forInvoice($entity, $body)->toArray()
        );
    }
}

// app/Modules/WhatsApp/Actions/LogWhatsAppMessageAction.php
class LogWhatsAppMessageAction {
    public function execute(WhatsAppMessage $message): void {
        // تسجيل في CRM Timeline إذا كان هناك عميل
        if ($message->client_id) {
            app(LogClientActivityAction::class)->execute(
                clientId: $message->client_id,
                type: ClientActivityType::WhatsAppSent,
                description: "رسالة واتساب: {$message->type->label()}",
                metadata: ['message_id' => $message->id, 'phone' => $message->phone]
            );
        }
    }
}

// app/Modules/WhatsApp/Actions/SendWhatsAppReminderAction.php
class SendWhatsAppReminderAction {
    public function __construct(
        private PrepareWhatsAppMessageAction $prepare,
        private LogWhatsAppMessageAction $log,
    ) {}

    public function execute(Invoice $invoice): WhatsAppMessage {
        $message = $this->prepare->execute(
            WhatsAppTemplateType::InvoiceReminder,
            $invoice
        );
        
        $this->log->execute($message);
        
        event(new WhatsAppMessagePrepared($message));
        
        return $message;
    }
}
```

### 4.5 Service

```php
// app/Modules/WhatsApp/Services/WhatsAppService.php
class WhatsAppService {
    public function __construct(
        private PrepareWhatsAppMessageAction $prepare,
        private LogWhatsAppMessageAction $log,
    ) {}

    /**
     * إرسال فاتورة عبر واتساب
     */
    public function sendInvoice(Invoice $invoice, ?int $templateId = null): WhatsAppMessage {
        return $this->send(WhatsAppTemplateType::InvoiceSent, $invoice, $templateId);
    }

    /**
     * إرسال عرض سعر عبر واتساب
     */
    public function sendQuote(Quote $quote, ?int $templateId = null): WhatsAppMessage {
        return $this->send(WhatsAppTemplateType::QuoteSent, $quote, $templateId);
    }

    /**
     * إرسال تذكير دفع
     */
    public function sendPaymentReminder(Invoice $invoice): WhatsAppMessage {
        return $this->send(WhatsAppTemplateType::InvoiceReminder, $invoice);
    }

    /**
     * إرسال تذكير متأخر
     */
    public function sendOverdueReminder(Invoice $invoice): WhatsAppMessage {
        return $this->send(WhatsAppTemplateType::InvoiceOverdue, $invoice);
    }

    /**
     * إرسال تحديث مشروع
     */
    public function sendProjectUpdate(Project $project, string $update): WhatsAppMessage {
        return $this->send(WhatsAppTemplateType::ProjectUpdate, $project, null, ['project_update' => $update]);
    }

    /**
     * الإرسال الأساسي
     */
    private function send(
        WhatsAppTemplateType $type,
        Model $entity,
        ?int $templateId = null,
        array $extraVars = []
    ): WhatsAppMessage {
        $message = $this->prepare->execute($type, $entity, $templateId, $extraVars);
        $this->log->execute($message);
        event(new WhatsAppMessagePrepared($message));
        return $message;
    }

    /**
     * بناء رابط wa.me
     */
    public function buildWameLink(WhatsAppMessage $message): string {
        return $message->buildWameLink();
    }

    /**
     * الحصول على إعدادات المستخدم
     */
    public function getSettings(int $userId): WhatsAppSettings {
        return WhatsAppSettings::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * التحقق من صلاحية الخطة
     */
    public function canSend(User $user): bool {
        return $user->subscription_plan !== SubscriptionPlan::Free;
    }
}
```

### 4.6 Events

```php
// app/Modules/WhatsApp/Events/WhatsAppMessagePrepared.php
class WhatsAppMessagePrepared {
    public function __construct(public readonly WhatsAppMessage $message) {}
}

// app/Modules/WhatsApp/Events/WhatsAppMessageSent.php
class WhatsAppMessageSent {
    public function __construct(
        public readonly WhatsAppMessage $message,
        public readonly string $provider
    ) {}
}

// app/Modules/WhatsApp/Events/WhatsAppMessageDelivered.php
class WhatsAppMessageDelivered {
    public function __construct(public readonly WhatsAppMessage $message) {}
}
```

### 4.7 Jobs & Queue

```php
// app/Modules/WhatsApp/Jobs/SendWhatsAppReminderJob.php
class SendWhatsAppReminderJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300; // 5 دقائق بين المحاولات

    public function __construct(
        private readonly int $invoiceId,
        private readonly WhatsAppTemplateType $type
    ) {}

    public function handle(WhatsAppService $service): void {
        $invoice = Invoice::find($this->invoiceId);
        if (!$invoice || $invoice->status === InvoiceStatus::Paid) return;

        match ($this->type) {
            WhatsAppTemplateType::InvoiceReminder => $service->sendPaymentReminder($invoice),
            WhatsAppTemplateType::InvoiceOverdue  => $service->sendOverdueReminder($invoice),
            default => null,
        };
    }
}

// app/Modules/WhatsApp/Jobs/ProcessWhatsAppWebhookJob.php
class ProcessWhatsAppWebhookJob implements ShouldQueue {
    public function handle(): void {
        // معالجة webhook من WhatsApp Business API (مستقبلي)
        // تحديث حالة الرسالة: delivered / read
    }
}
```

### 4.8 Controller

```php
// app/Modules/WhatsApp/Http/Controllers/WhatsAppController.php
class WhatsAppController extends Controller {

    public function __construct(private WhatsAppService $whatsapp) {}

    // عرض مركز واتساب
    public function index(): View {
        $messages = WhatsAppMessage::latest()->paginate(20);
        $stats = $this->buildStats();
        return view('whatsapp.index', compact('messages', 'stats'));
    }

    // إرسال فاتورة عبر واتساب
    public function sendInvoice(Invoice $invoice, SendWhatsAppRequest $request): JsonResponse {
        $this->authorize('view', $invoice);

        if (!$this->whatsapp->canSend(auth()->user())) {
            return response()->json(['error' => 'يتطلب خطة Pro أو أعلى'], 403);
        }

        $message = $this->whatsapp->sendInvoice($invoice, $request->template_id);
        $link = $this->whatsapp->buildWameLink($message);

        return response()->json([
            'success' => true,
            'link'    => $link,
            'message_id' => $message->ulid,
        ]);
    }

    // إرسال عرض سعر عبر واتساب
    public function sendQuote(Quote $quote, SendWhatsAppRequest $request): JsonResponse {
        $this->authorize('view', $quote);
        $message = $this->whatsapp->sendQuote($quote, $request->template_id);
        return response()->json([
            'success' => true,
            'link'    => $this->whatsapp->buildWameLink($message),
        ]);
    }

    // صفحة القوالب
    public function templates(): View {
        return view('whatsapp.templates', [
            'templates' => WhatsAppTemplate::orderBy('type')->get(),
        ]);
    }

    // حفظ قالب
    public function storeTemplate(StoreWhatsAppTemplateRequest $request): RedirectResponse {
        WhatsAppTemplate::create($request->validated() + ['user_id' => auth()->id()]);
        return back()->with('success', 'تم حفظ القالب');
    }

    // الإعدادات
    public function settings(): View {
        $settings = $this->whatsapp->getSettings(auth()->id());
        return view('whatsapp.settings', compact('settings'));
    }

    // حفظ الإعدادات
    public function saveSettings(SaveWhatsAppSettingsRequest $request): RedirectResponse {
        $this->whatsapp->getSettings(auth()->id())->update($request->validated());
        return back()->with('success', 'تم حفظ الإعدادات');
    }

    // تحديث حالة الرسالة (عند فتح الرابط)
    public function markOpened(WhatsAppMessage $message): JsonResponse {
        $this->authorize('update', $message);
        $message->markAsOpened();
        return response()->json(['success' => true]);
    }
}
```

### 4.9 Listeners

```php
// app/Modules/WhatsApp/Listeners/LogWhatsAppToTimeline.php
class LogWhatsAppToTimeline {
    public function handle(WhatsAppMessagePrepared $event): void {
        $message = $event->message;
        if (!$message->client_id) return;

        app(LogClientActivityAction::class)->execute(
            clientId: $message->client_id,
            type: ClientActivityType::WhatsAppSent,
            description: $this->buildDescription($message),
            metadata: [
                'message_ulid' => $message->ulid,
                'type'         => $message->type,
                'phone'        => $message->phone,
            ]
        );
    }

    private function buildDescription(WhatsAppMessage $message): string {
        return match ($message->type) {
            'invoice_sent'     => "أُرسلت الفاتورة {$message->messageable?->number} عبر واتساب",
            'invoice_reminder' => "تم إرسال تذكير الدفع للفاتورة {$message->messageable?->number}",
            'quote_sent'       => "أُرسل عرض السعر {$message->messageable?->number} عبر واتساب",
            default            => "رسالة واتساب: {$message->type}",
        };
    }
}
```

### 4.10 Scheduler — التذكيرات التلقائية

```php
// routes/console.php — إضافة جديدة
$schedule->command('whatsapp:send-reminders')->dailyAt('09:00');
// ↳ يُرسل تذكيرات للفواتير المستحقة خلال X أيام (حسب إعدادات المستخدم)
// ↳ يُرسل تذكيرات للفواتير المتأخرة (overdue)
// ↳ يُسجل في CRM Timeline تلقائياً
```

```php
// app/Console/Commands/WhatsApp/SendWhatsAppRemindersCommand.php
class SendWhatsAppRemindersCommand extends Command {
    protected $signature = 'whatsapp:send-reminders';

    public function handle(WhatsAppService $service): void {
        // 1. المستخدمون الذين فعّلوا التذكيرات التلقائية
        $settings = WhatsAppSettings::where('auto_reminder_enabled', true)
            ->where('is_enabled', true)
            ->with('user')
            ->get();

        foreach ($settings as $setting) {
            // 2. الفواتير المستحقة قريباً
            $upcoming = Invoice::where('user_id', $setting->user_id)
                ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
                ->whereDate('due_date', now()->addDays($setting->reminder_days_before))
                ->get();

            foreach ($upcoming as $invoice) {
                SendWhatsAppReminderJob::dispatch($invoice->id, WhatsAppTemplateType::InvoiceReminder)
                    ->onQueue('whatsapp');
            }
        }
    }
}
```

---

## 5. واجهة المستخدم (UI/UX Flow)

### 5.1 نقاط الوصول

```
┌──────────────────────────────────────────────────────────────┐
│  مركز واتساب الأعمال — /whatsapp                             │
│  ─────────────────────────────────────────────────────────── │
│  📊 الإحصائيات: إجمالي المُرسَل | هذا الشهر | المعلّقة      │
│                                                              │
│  [📋 سجل الرسائل] [📝 القوالب] [⚙️ الإعدادات]              │
│  ─────────────────────────────────────────────────────────── │
│  قائمة الرسائل:                                              │
│  📱 INV-0023 → أحمد محمد | فاتورة | مُرسَلة ✓ | 2 يونيو   │
│  📱 QUO-0011 → سارة علي | عرض سعر | مفتوح 👁 | 1 يونيو    │
└──────────────────────────────────────────────────────────────┘
```

### 5.2 زر واتساب في صفحة الفاتورة

```
┌─ صفحة الفاتورة INV-0023 ─────────────────────────────────┐
│  [📤 إرسال] [🖨 طباعة] [✅ تسجيل دفع] [📱 واتساب ▼]     │
│                                          ┌──────────────┐ │
│                                          │ 📱 إرسال فاتورة│ │
│                                          │ 🔔 تذكير دفع  │ │
│                                          │ ⚠️ إشعار تأخر │ │
│                                          └──────────────┘ │
└───────────────────────────────────────────────────────────┘
```

### 5.3 Modal معاينة الرسالة

```
┌─ معاينة رسالة واتساب ─────────────────────────────────────┐
│                                                            │
│  📞 رقم: +966-5xxxxxxxx                                   │
│  📝 القالب: [فاتورة مُرسَلة ▼]                             │
│                                                            │
│  ┌── الرسالة ────────────────────────────────────────┐    │
│  │ مرحباً أحمد،                                       │    │
│  │ نرسل لك فاتورة رقم INV-0023 بقيمة 5,000 ₪        │    │
│  │ تاريخ الاستحقاق: 15 يونيو 2026                    │    │
│  │                                                    │    │
│  │ مع تحيات، محمد الأمين                             │    │
│  └────────────────────────────────────────────────────┘   │
│                                                            │
│  [تعديل] [إرسال عبر واتساب →]                            │
└────────────────────────────────────────────────────────────┘
```

### 5.4 صفحة القوالب

```
┌─ قوالب واتساب ────────────────────────────────────────────┐
│  [+ قالب جديد]                                            │
│                                                            │
│  📌 قوالب افتراضية                                        │
│  ├─ فاتورة مُرسَلة       [معاينة] [تعديل]                │
│  ├─ تذكير دفع            [معاينة] [تعديل]                │
│  ├─ فاتورة متأخرة        [معاينة] [تعديل]                │
│  ├─ عرض سعر              [معاينة] [تعديل]                │
│  └─ متابعة عرض           [معاينة] [تعديل]                │
│                                                            │
│  ✏️ قوالبي المخصصة                                        │
│  └─ (لا توجد قوالب مخصصة)                               │
└────────────────────────────────────────────────────────────┘
```

---

## 6. تقييد خطط الاشتراك (SaaS Plan Restrictions)

| الميزة | مجاني | Pro | Business |
|--------|-------|-----|----------|
| إرسال يدوي عبر wa.me | ❌ | ✅ غير محدود | ✅ غير محدود |
| قوالب واتساب مخصصة | ❌ | ✅ حتى 5 | ✅ غير محدود |
| تذكيرات آلية | ❌ | ✅ | ✅ |
| سجل الرسائل | ❌ | ✅ 90 يوماً | ✅ غير محدود |
| تقارير واتساب | ❌ | ✅ أساسية | ✅ متقدمة |
| WhatsApp Business API | ❌ | ❌ | ✅ مستقبلاً |
| تتبع الإرسال/القراءة | ❌ | ❌ | ✅ مستقبلاً |

**Upgrade Triggers:**
- عند محاولة إرسال في الخطة المجانية → Modal ترقية
- عند إنشاء القالب السادس في Pro → Modal ترقية

---

## 7. Routes

```php
// routes/whatsapp.php
Route::middleware(['auth', 'verified', 'active'])->prefix('whatsapp')->name('whatsapp.')->group(function () {

    Route::get('/', [WhatsAppController::class, 'index'])->name('index');
    Route::get('/templates', [WhatsAppController::class, 'templates'])->name('templates');
    Route::post('/templates', [WhatsAppController::class, 'storeTemplate'])->name('templates.store');
    Route::put('/templates/{template}', [WhatsAppController::class, 'updateTemplate'])->name('templates.update');
    Route::delete('/templates/{template}', [WhatsAppController::class, 'destroyTemplate'])->name('templates.destroy');
    Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings');
    Route::post('/settings', [WhatsAppController::class, 'saveSettings'])->name('settings.save');

    // إرسال
    Route::post('/send/invoice/{invoice}', [WhatsAppController::class, 'sendInvoice'])->name('send.invoice');
    Route::post('/send/quote/{quote}', [WhatsAppController::class, 'sendQuote'])->name('send.quote');
    Route::post('/send/reminder/{invoice}', [WhatsAppController::class, 'sendReminder'])->name('send.reminder');
    Route::post('/send/project/{project}', [WhatsAppController::class, 'sendProjectUpdate'])->name('send.project');

    // تتبع
    Route::post('/messages/{message}/opened', [WhatsAppController::class, 'markOpened'])->name('messages.opened');
});
```

---

## 8. استراتيجية التطور للـ WhatsApp Business API

### المرحلة الحالية (wa.me)
```
User → دراهم → يُنشئ رسالة → يُعيد رابط wa.me → المستخدم يُرسل يدوياً
```

### المرحلة القادمة (API)
```
User → دراهم → API Provider → WhatsApp → يُرسل تلقائياً
                             ↓
                        Webhook ← حالة الإرسال
                             ↓
                    دراهم يُحدّث السجل
```

### `WhatsAppProviderInterface`
```php
interface WhatsAppProviderInterface {
    public function send(string $phone, string $body): SendResult;
    public function sendTemplate(string $phone, string $templateName, array $params): SendResult;
    public function getDeliveryStatus(string $messageId): DeliveryStatus;
    public function validateWebhook(Request $request): bool;
}

// Implementations:
class WameProvider implements WhatsAppProviderInterface { /* wa.me deep-link */ }
class TwilioWhatsAppProvider implements WhatsAppProviderInterface { /* Twilio API */ }
class MetaWhatsAppProvider implements WhatsAppProviderInterface { /* Meta Business API */ }
```

---

## 9. Migrations

```bash
# الترتيب:
php artisan make:migration create_whatsapp_settings_table
php artisan make:migration create_whatsapp_templates_table
php artisan make:migration create_whatsapp_messages_table
php artisan make:migration add_whatsapp_channel_to_client_activities  # إضافة نوع للـ enum
```

---

## 10. Seeder — القوالب الافتراضية

```php
// database/seeders/WhatsAppTemplateSeeder.php
class WhatsAppTemplateSeeder extends Seeder {
    public function run(): void {
        $defaults = [
            [
                'type'       => 'invoice_sent',
                'name'       => 'إرسال فاتورة (افتراضي)',
                'body'       => "مرحباً {client_name},\n\nنرسل لك فاتورة رقم {invoice_number} بقيمة {invoice_total}.\nتاريخ الاستحقاق: {invoice_due_date}\n\nمع تحياتنا,\n{user_name}",
                'is_default' => true,
            ],
            [
                'type'       => 'invoice_reminder',
                'name'       => 'تذكير دفع (افتراضي)',
                'body'       => "مرحباً {client_name},\n\nتذكير بأن الفاتورة رقم {invoice_number} بقيمة {invoice_total} ستستحق في {invoice_due_date}.\n\nنرجو التكرم بالسداد في الموعد المحدد.\n\n{user_name}",
                'is_default' => true,
            ],
            // ... المزيد
        ];
        
        // تُضاف للمستخدمين الجدد عند التسجيل عبر Observer أو Event
    }
}
```

---

## 11. اعتبارات الأمان

| المخاطرة | الحل |
|---------|-------|
| حقن في نص الرسالة | Blade escaping + `htmlspecialchars` قبل URL encode |
| إرسال لرقم غير مصرّح | التحقق من ملكية الكيان (Policy) قبل الإرسال |
| CSRF | جميع طلبات POST محمية بـ CSRF token |
| Rate limiting | 30 رسالة/ساعة لمنع الإساءة |
| تخزين مفاتيح API | تشفير AES-256 في قاعدة البيانات |
| بيانات العملاء | BelongsToUser Global Scope يحمي تلقائياً |

---

## 12. KPIs والمقاييس

```
مقاييس مركز واتساب:
├── إجمالي الرسائل المُرسَلة (شهرياً)
├── رسائل الفواتير vs التذكيرات vs العروض
├── معدل التحويل: فاتورة مُرسَلة عبر واتساب → مدفوعة
├── متوسط وقت الدفع بعد التذكير
└── أكثر الأنواع استخداماً
```

---

*آخر تحديث: 8 يونيو 2026 — Phase 26 Sprint 1*
