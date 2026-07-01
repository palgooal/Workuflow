# التحصيل عبر دراهم (Payment Collection)

> تاريخ الإضافة: 1 يوليو 2026 | آخر تحديث: 1 يوليو 2026 | الإصدار: 1.7.1

---

## Overview

ميزة "التحصيل عبر دراهم" تسمح لعميل المستقل بدفع فاتورته مباشرة عبر رابط دفع عام، دون الحاجة لتحويل بنكي يدوي أو تنسيق خارج المنصة. الدفع يمر عبر بوابة الدفع (Togo حالياً) بحساب **دراهم** نفسه، وليس حساب المستقل — أي أن المنصة تُحصِّل المبلغ **نيابة عن المشترك**، ثم تُسوّي الأموال معه يدوياً لاحقاً.

هذا يختلف عن `MANUAL-BILLING-FLOW.md` (فوترة اشتراك دراهم نفسها) — هنا الحديث عن تحصيل فواتير **عملاء المستقلين**.

**لا توجد payouts تلقائية في هذا الإصدار.** التسوية مع المشترك (تحويل المبلغ الصافي له) عملية يدوية بحتة، تُنفَّذ حالياً بتحديث `payment_collections.status` إلى `settled` مباشرة (لا واجهة إدارية بعد).

---

## Business Requirements

| المتطلب | الحل |
|---------|------|
| صفحة دفع عامة للفاتورة (بدون تسجيل دخول) | `GET /pay/invoice/{invoice:ulid}` |
| تحصيل عبر بوابة الدفع نيابة عن المشترك | `TogoPaymentService::createInvoicePaymentOrder()` |
| تسجيل كل عملية تحصيل | جدول `payment_collections` |
| عدم كسر تسجيل الدفع اليدوي الحالي | منطق `markPaid` مُستخرَج إلى `InvoicePaymentService` ومُعاد استخدامه من المسارين |
| عزل بيانات المستخدمين (Multi-tenancy) | مسارات `/pay/*` عامة عمداً وهي الاستثناء الوحيد — بلا أي قيد `user_id` آخر في التطبيق |
| عدم تنفيذ payouts تلقائية | لا يوجد أي كود يُحرِّك الأموال فعلياً للمشترك — `status` فقط |
| تسوية يدوية لاحقاً | `status = settled` + `settled_at` — تُحدَّث يدوياً حالياً |

---

## Database Structure

### payment_collections

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint PK | | | |
| user_id | FK → users | | | المشترك الذي تُحصَّل الفاتورة نيابة عنه — cascadeOnDelete |
| invoice_id | FK → invoices | | | cascadeOnDelete |
| client_id | FK → clients | | | العميل الدافع — cascadeOnDelete |
| provider | varchar(50) | | 'togo' | مزود بوابة الدفع |
| provider_payment_id | varchar | ✓ | NULL | معرّف الطلب لدى المزود (Togo order id) |
| amount | decimal(12,2) | | | مبلغ الفاتورة كما أنشأها المستقل — **لا يتغيّر أبداً** |
| currency | char(3) | | 'ILS' | عملة الفاتورة كما أنشأها المستقل |
| platform_fee | decimal(12,2) | | 0 | ⚠️ **legacy/غير مُعتمَد للتسوية الفعلية منذ v1.6.0** — عمولة مُحسَبة بعملة الفاتورة (`currency`)، تبقى محفوظة للتوافق التاريخي فقط. استخدم `settlement_platform_fee` |
| net_amount | decimal(12,2) | | | ⚠️ **legacy/غير مُعتمَد للتسوية الفعلية منذ v1.6.0** — نفس ملاحظة `platform_fee` أعلاه. استخدم `settlement_net_amount` |
| settlement_currency | char(3) | | 'ILS' | عملة التسوية الفعلية — بوابة الدفع (Togo) تُسوِّي بالشيكل دائماً حالياً |
| settlement_amount | decimal(12,2) | ✓ | NULL | المبلغ الفعلي بعملة التسوية (`settlement_currency`) — `NULL` حتى يُعرَف (من رد Togo، أو مباشرة إن كانت الفاتورة بالشيكل أصلاً، أو بتأكيد يدوي من الأدمن لاحقاً) |
| settlement_platform_fee | decimal(12,2) | | 0 | عمولة بوابة الدفع الفعلية، محسوبة على `settlement_amount` (بعملة التسوية) — **المصدر الصحيح للعمولة الفعلية** |
| settlement_net_amount | decimal(12,2) | ✓ | NULL | `settlement_amount − settlement_platform_fee` — **هذا فقط ما يُحوَّل فعلياً للمشترك عند التسوية**. `NULL` إن كان `settlement_amount` غير معروف بعد |
| exchange_rate | decimal(12,6) | ✓ | NULL | سعر الصرف المُستخدَم لتحويل `amount` (عملة الفاتورة) إلى `settlement_amount` (بالشيكل) — `1` إن كانت الفاتورة بالشيكل أصلاً، `NULL` إن لم يُحدَّد بعد |
| status | varchar(20) | | 'pending' | pending → collected → settled \| failed \| refunded |
| collected_at | timestamp | ✓ | NULL | وقت نجاح التحصيل من العميل |
| settled_at | timestamp | ✓ | NULL | وقت التسوية اليدوية مع المشترك |
| metadata | json | ✓ | NULL | استجابة Togo الخام + checkout_url + timeline + تفاصيل احتساب العمولة/التسوية |
| created_at / updated_at | timestamps | | | |

**Indexes:** `(user_id, status)`, `(invoice_id, status)`, `(provider, provider_payment_id)`

> ⚠️ **عملة الفاتورة ≠ عملة التسوية.** راجع قسم "عملة الفاتورة مقابل عملة التسوية (Settlement Currency)" أدناه قبل استخدام أي عمود مالي من هذا الجدول.

### PaymentCollectionStatus enum

`app/Support/Enums/PaymentCollectionStatus.php`

| القيمة | المعنى |
|--------|--------|
| `pending` | طلب دفع أُنشئ، لم يُحصَّل بعد (المستخدم على صفحة الدفع أو البوابة) |
| `collected` | تم تحصيل المبلغ من العميل بنجاح — لدى دراهم، بانتظار التسوية |
| `settled` | تمت تسوية المبلغ يدوياً مع المشترك (تحويل فعلي خارج النظام) |
| `failed` | فشلت عملية الدفع أو أُلغيت |
| `refunded` | تم استرجاع المبلغ للعميل |

---

## Routes

كل مسارات `/pay/*` **عامة عمداً بدون Auth** — الوصول يتم فقط عبر ULID الفاتورة (غير قابل للتخمين، 26 حرف). هذا هو الاستثناء الوحيد المسموح به للوصول لفاتورة لا تخص المستخدم الحالي؛ كل مسارات الفواتير الأخرى (`/invoices/*`) تبقى محمية بـ `auth` مع فلترة `user_id` كما هي.

```
GET  /pay/invoice/{invoice:ulid}            pay.invoice.show      صفحة الدفع العامة
POST /pay/invoice/{invoice:ulid}/checkout   pay.invoice.checkout  بدء الدفع عبر Togo
GET  /pay/invoice/{invoice:ulid}/callback   pay.invoice.callback  عودة Togo بعد الدفع
GET  /pay/invoice/{invoice:ulid}/cancel     pay.invoice.cancel    إلغاء من المستخدم
```

مُعرَّفة في `routes/web.php` خارج مجموعة `middleware(['auth', 'verified'])`، بجانب `invoices.public-view`.

---

## الملفات المهمة

| الملف | الوظيفة |
|-------|---------|
| `app/Http/Controllers/InvoicePaymentController.php` | `show()`, `checkout()`, `callback()`, `cancel()` |
| `app/Services/InvoicePaymentService.php` | منطق `markPaid` المشترك (مُستخرَج من `InvoiceController`) |
| `app/Models/PaymentCollection.php` | Model + علاقات `user`, `invoice`, `client` |
| `app/Support/Enums/PaymentCollectionStatus.php` | حالات التحصيل |
| `app/Modules/Billing/Services/TogoPaymentService.php` | `createInvoicePaymentOrder()`, `extractCommissionAmount()`, `extractSettlementAmount()`, `extractExchangeRate()` — إضافات جديدة، لا تُغيّر منطق الاشتراكات الحالي |
| `app/Filament/Pages/PaymentSettings.php` | قسم "عمولة تحصيل الفواتير" — Toggle + نسبة % + عمولة ثابتة (مصدر الحقيقة الوحيد لنسبة/قيمة العمولة، تُطبَّق على `settlement_amount` منذ v1.6.0) |
| `resources/views/invoices/pay.blade.php` | صفحة الدفع العامة (standalone، بدون layout) |
| `resources/views/invoices/show.blade.php` | زر "ادفع الآن" — يفتح رابط الدفع في تبويب جديد |
| `database/migrations/2026_07_01_000001_create_payment_collections_table.php` | |
| `database/migrations/2026_07_01_000002_add_unique_invoice_id_to_payment_collections_table.php` | تنظيف تكرارات `invoice_id` (إن وُجدت) ثم `unique(invoice_id)` — سجل تحصيل واحد لكل فاتورة |
| `database/migrations/2026_07_02_000001_add_settlement_columns_to_payment_collections_table.php` | يضيف `settlement_currency`/`settlement_amount`/`settlement_platform_fee`/`settlement_net_amount`/`exchange_rate` + يُنسِّق السجلات القديمة تلقائياً (راجع v1.6.0 أدناه) |
| `database/migrations/2026_07_02_000002_create_settlement_requests_table.php` | جدول `settlement_requests` (طلبات التسوية — v1.7.0) |
| `database/migrations/2026_07_02_000003_create_settlement_request_payment_collection_table.php` | جدول pivot يربط كل طلب بالتحصيلات التي شملها |
| `app/Models/SettlementRequest.php` | Model + علاقة `paymentCollections()` (belongsToMany) |
| `app/Support/Enums/SettlementRequestStatus.php` | حالات طلب التسوية: pending/approved/rejected/paid |
| `app/Http/Controllers/SettlementRequestController.php` | `store()` — إنشاء طلب تسوية من المشترك فقط |
| `app/Filament/Resources/SettlementRequestResource.php` | مراجعة/اعتماد/رفض/تعليم كمدفوع من الأدمن |
| `app/Filament/Resources/PaymentCollectionResource/Widgets/AwaitingSettlementAmountWidget.php` | تنبيه الأدمن بعدد التحصيلات بحاجة تحديد مبلغ تسوية |
| `app/Console/Commands/BackfillPaidSettlementRequests.php` | أمر إصلاح لمرة واحدة (v1.7.1) — `settlement-requests:backfill-paid` |

---

## تدفق الدفع (Happy Path)

```
المستقل يضغط "ادفع الآن" في /invoices/{ulid}  → يفتح /pay/invoice/{ulid} (نفس الرابط يُشارَك مع العميل)
  → العميل يضغط "ادفع الآن" → POST /pay/invoice/{ulid}/checkout
    → InvoicePaymentController::checkout()
        - يتحقق: غير مدفوعة، غير ملغاة، البوابة مفعّلة، يوجد بريد إلكتروني
        - TogoPaymentService::createInvoicePaymentOrder() → RFP order لدى Togo
        - ينشئ/يُحدِّث PaymentCollection (status = pending)
        - Redirect خارجي → صفحة دفع Togo
  → العميل يدفع على Togo → Redirect إلى GET /pay/invoice/{ulid}/callback
    → InvoicePaymentController::callback()
        - TogoPaymentService::verifyOrder() يتحقق من حالة الطلب فعلياً (لا يثق بمجرد الرجوع للرابط)
        - عند النجاح: DB::transaction تُنفِّذ معاً:
            1) InvoicePaymentService::markPaid($invoice, walletId: null)
               → invoice.status = paid + Transaction دخل (بلا wallet_id — بانتظار التسوية)
               → تحديث Client.total_paid / total_revenue / last_payment_at
               → RecalculateClientHealthScoreJob (مؤجَّلة)
            2) PaymentCollection.status = collected + collected_at = now()
        - Redirect → /pay/invoice/{ulid} مع رسالة نجاح
```

عند الإلغاء من بوابة Togo: `GET /pay/invoice/{ulid}/cancel` يُعيد المستخدم لصفحة الدفع برسالة، دون تغيير حالة `PaymentCollection` (يبقى `pending` ويُعاد استخدامه/تحديثه في المحاولة التالية بدل تكديس سجلات يتيمة).

---

## إعادة استخدام منطق markPaid (بدون كسره)

`InvoiceController::markPaid()` (التسجيل اليدوي داخل التطبيق — يختار المستخدم صندوقاً) و`InvoicePaymentController::callback()` (التحصيل عبر البوابة — بلا صندوق) يستدعيان الآن نفس `InvoicePaymentService::markPaid()`:

- تحديث `invoice.status = paid` + `paid_at`
- إنشاء `Transaction` دخل (`wallet_id` اختياري — `null` في مسار البوابة)
- تحديث إحصائيات العميل (`refreshClientFinancials()`)
- جدولة `RecalculateClientHealthScoreJob`

سلوك `InvoiceController::markPaid()` (validation، رسائل flash، اشتراط `wallet_id`) لم يتغيّر — الاستخراج شفّاف بالكامل لواجهة المستخدم الحالية.

---

## لماذا `wallet_id = null` عند التحصيل عبر البوابة؟

الأموال المُحصَّلة من العميل تصل فعلياً لحساب **دراهم** على Togo، وليس لصندوق المشترك مباشرة — لذلك لا معنى لربطها بصندوق محدد قبل التسوية الفعلية. الحقل `wallet_id` في `transactions` قابل لـ NULL أصلاً، فالمعاملة تُسجَّل كدخل (للتقارير المحاسبية للمشترك) لكن بلا صندوق حتى تتم التسوية اليدوية.

---

## عملة الفاتورة مقابل عملة التسوية (Settlement Currency) — v1.6.0

**المشكلة التي عولجت في هذا الإصدار:** بوابة الدفع (Togo) تُحصِّل وتُسوِّي الأموال فعلياً **بالشيكل (ILS) دائماً**، بغض النظر عن عملة الفاتورة التي أنشأها المستقل. قبل v1.6.0 كان `net_amount`/`platform_fee` يُحسبان بعملة الفاتورة نفسها (`currency`) — وهذا صحيح فقط بالصدفة عندما تكون الفاتورة بالشيكل أصلاً، لكنه **مضلِّل تماماً لأي فاتورة بعملة أخرى** (مثلاً USD): كان يُعرَض "صافي 950 USD" بينما المبلغ الذي ستستلمه Togo فعلياً هو رقم بالشيكل قد يختلف تماماً عن 950 حسب سعر الصرف.

**الحل:** فصل كامل بين مفهومين:

| المفهوم | الأعمدة | يمثّل |
|---|---|---|
| **عملة الفاتورة (Invoice currency)** | `amount`, `currency` | ما أنشأه المستقل في الفاتورة — **لا يتغيّر أبداً**، ولا علاقة له بما سيصل فعلياً للمشترك |
| **عملة التسوية (Settlement currency)** | `settlement_currency`, `settlement_amount`, `settlement_platform_fee`, `settlement_net_amount`, `exchange_rate` | ما ستُحصِّله/تُسوِّيه بوابة الدفع فعلياً بالشيكل — **هذا وحده المصدر الصحيح لما سيُحوَّل للمشترك** |

### آلية تحديد settlement_amount (`InvoicePaymentController@callback`)

عند نجاح الدفع، بهذا الترتيب من الأولوية:

1. **مبلغ تسوية صريح من رد Togo** (`TogoPaymentService::extractSettlementAmount()`) — إن توفّر، يُستخدَم مباشرة. `settlement_source = togo_response`.
2. **سعر صرف صريح من رد Togo بدون مبلغ** (`extractExchangeRate()`) — يُحسَب `settlement_amount = amount × exchange_rate`. `settlement_source = togo_response`.
3. **الفاتورة بالشيكل أصلاً** (`currency = ILS`) — لا تحويل مطلوب: `exchange_rate = 1`، `settlement_amount = amount`. `settlement_source = same_currency`.
4. **لا شيء مما سبق** (فاتورة بعملة أجنبية وTogo لم تُرجِع مبلغاً أو سعر صرف) — **لا نفترض أي سعر صرف إطلاقاً** (لا نساوي مثلاً USD بـ ILS بشكل اعتباطي). يبقى `settlement_amount = NULL`، ويُسجَّل `Log::warning()`. `settlement_source = pending_admin_review`.

Togo لا توثّق حالياً حقلاً رسمياً ثابتاً لمبلغ التسوية أو سعر الصرف — الاستخراج في الحالتين (1) و(2) هو "قدر المستطاع" لعدة أسماء حقول محتملة، تماماً بنفس أسلوب `extractCommissionAmount()`.

### عمولة التسوية (`settlement_platform_fee`)

تُحسَب **فقط على `settlement_amount` (بالشيكل)**، وليس على مبلغ الفاتورة:

```
إن كانت settlement_amount غير معروفة (NULL):
    settlement_platform_fee = 0   (لا تُحسب)
    settlement_net_amount   = NULL (لا يُحسب)
    → status يبقى collected لكن التسوية مؤجَّلة حتى تُعرف settlement_amount

إن كانت settlement_amount معروفة:
    إن كانت invoice_collection_fee_enabled = false → settlement_platform_fee = 0
    وإلا → settlement_platform_fee = (settlement_amount × fee_rate ÷ 100) + fixed_fee
    settlement_net_amount = max(0, settlement_amount − settlement_platform_fee)
```

`fee_rate`/`fixed_fee` تُقرآن من نفس إعدادات لوحة الإدارة (`invoice_collection_fee_rate`/`invoice_collection_fixed_fee`) الموضّحة في القسم التالي — لا إعداد منفصل. تُحفَظ في `metadata`: `settlement_source`, `settlement_fee_rate`, `settlement_fixed_fee`.

### تحديد settlement_amount يدوياً من لوحة الإدارة

لفواتير العملة الأجنبية التي وصلت لحالة `pending_admin_review` (أو السجلات القديمة قبل v1.6.0 — راجع "التوافق مع السجلات القديمة" أدناه)، يظهر في `PaymentCollectionResource` زر **"تحديد مبلغ التسوية يدوياً"** (يظهر فقط عندما `status = collected` و`settlement_amount = NULL`) — يطلب من الأدمن إدخال المبلغ الفعلي بالشيكل (بعد التحقق من لوحة Togo) وسعر صرف اختياري للتوثيق، ثم يحسب `settlement_platform_fee`/`settlement_net_amount` تلقائياً بنفس الصيغة أعلاه ويحفظ `settlement_source = admin_manual`. بعدها يصبح زر **"تسوية مع المشترك"** متاحاً.

### التوافق مع السجلات القديمة (قبل v1.6.0)

Migration `2026_07_02_000001` تُنسِّق كل سجل موجود تلقائياً عند الترقية:

| حالة السجل القديم | النتيجة بعد الترقية |
|---|---|
| `currency = ILS` | `settlement_currency = ILS`، `settlement_amount = amount`، `settlement_platform_fee = platform_fee`، `settlement_net_amount = net_amount`، `exchange_rate = 1` — نسخ مباشر لأن القيم القديمة كانت صحيحة فعلياً (بالصدفة) |
| `currency != ILS` | `settlement_currency = ILS`، `settlement_amount = NULL`، `settlement_net_amount = NULL` — **تحتاج مراجعة يدوية من الأدمن** (زر "تحديد مبلغ التسوية يدوياً") قبل أن تُصبح قابلة للتسوية، حتى لو كانت `status` القديمة `collected` أو حتى `settled` |

لا تُحذف ولا تُعدَّل أي فاتورة أو معاملة أثناء هذا التنسيق — فقط أعمدة `payment_collections` الجديدة.

---

## عمولة بوابة الدفع (platform_fee) — v1.4.0 (⚠️ legacy — راجع القسم أعلاه)

> **هذا القسم يوثّق `platform_fee`/`net_amount` القديمين بعملة الفاتورة، المحفوظين للتوافق التاريخي فقط منذ v1.6.0.** للعمولة والصافي الفعليين المُعتمَدين للتسوية، راجع "عملة الفاتورة مقابل عملة التسوية" أعلاه (`settlement_platform_fee`/`settlement_net_amount`).

الفكرة الأساسية: **الفاتورة تبقى دائماً بقيمتها الكاملة، والعمولة تُخصم فقط من التحصيل — لا من الفاتورة نفسها. والتسوية مع المشترك تتم على الصافي بعد خصم العمولة، وليس على مبلغ الفاتورة الكامل.**

| الكيان | القيمة | يتأثر بالعمولة؟ |
|--------|--------|------------------|
| `invoices.total` | قيمة الفاتورة كما أنشأها المستقل | **لا** — لا يتغيّر إطلاقاً |
| `transactions.amount` (معاملة الدخل التلقائية) | = `invoice.total` كاملاً | **لا** — يمثّل ما دفعه العميل فعلياً، وليس ما سيصل للمشترك |
| `payment_collections.amount` | = `invoice.total` وقت إنشاء طلب الدفع | **لا** — نفس مبلغ الفاتورة |
| `payment_collections.platform_fee` | عمولة البوابة عن هذا التحصيل | **نعم** — يُحسب فقط عند نجاح الدفع |
| `payment_collections.net_amount` | `amount − platform_fee` | **نعم** — هذا فقط ما يُحوَّل فعلياً للمشترك عند التسوية |

### ⚠️ العمولة تُدار من لوحة الإدارة، وليس من `.env`/`config`

منذ v1.4.0، **لا يوجد أي إعداد للعمولة في `config/billing.php` أو `.env`**. الإدارة الكاملة عبر:

**Filament → بوابة الدفع** (`app/Filament/Pages/PaymentSettings.php`، قسم "عمولة تحصيل الفواتير") — تُخزَّن القيم في نفس جدول `settings` الذي يستخدمه باقي إعدادات بوابة الدفع (`group = 'payment'`، بنفس نمط `Setting::get()`/`Setting::set()` الموجود مسبقاً في المشروع — لم يُضَف نظام إعدادات جديد):

| المفتاح (`settings.key`) | Filament Component | الافتراضي | الوصف |
|---|---|---|---|
| `invoice_collection_fee_enabled` | Toggle "تفعيل عمولة تحصيل الفواتير" | `true` (`'1'`) | عند التعطيل → `platform_fee = 0` لكل التحصيلات الجديدة |
| `invoice_collection_fee_rate` | TextInput "نسبة العمولة %" | `2.5` | نسبة مئوية — `2.5` تعني 2.5% (يُقسَم على 100 عند الحساب) |
| `invoice_collection_fixed_fee` | TextInput "عمولة ثابتة" | `0` | مبلغ ثابت بعملة التحصيل يُضاف على كل عملية |

### آلية الحساب (`InvoicePaymentController@callback`)

عند نجاح التحقق من Togo، قبل تعليم `PaymentCollection` كـ `collected`، بهذا الترتيب من الأولوية:

1. **من رد المزود أولاً** — `TogoPaymentService::extractCommissionAmount($togoData)` يفحص عدة أسماء حقول محتملة (`commission_amount`, `commission`, `fee_amount`, `fee`, `platform_fee`, أو `fees.{commission|platform|gateway}`) في استجابة `verifyOrder()`. Togo لا توثّق حقلاً رسمياً ثابتاً لعمولة الـ RFP حالياً، لذا هذا استخراج "قدر المستطاع" — يُعيد `null` إن لم يجد شيئاً. عند التوفر: `platform_fee_source = togo_response`، ولا تُستخدم إعدادات الأدمن إطلاقاً.
2. **إعدادات لوحة الإدارة إن لم تتوفر عمولة من المزود:**
   - إن كانت `invoice_collection_fee_enabled = false` → `platform_fee = 0`، `platform_fee_source = disabled`.
   - إن كانت مفعّلة → `platform_fee = (amount × fee_rate ÷ 100) + fixed_fee`، `platform_fee_source = admin_settings`.
3. `net_amount = max(0, amount − platform_fee)`، وكلاهما يُقرَّب لمنزلتين عشريتين.
4. يُحفَظ في `metadata`: `platform_fee_source` (`togo_response` | `admin_settings` | `disabled`)، و`fee_rate`/`fixed_fee` (القيمتان المُستخدمتان فعلياً وقت الحساب — `null` إن كان المصدر `togo_response` أو `disabled`) — سجل تاريخي دائم حتى لو غيّر الأدمن الإعدادات لاحقاً.

قبل هذه اللحظة (حالة `pending`)، يبقى `platform_fee = 0` و`net_amount = amount` — العمولة الحقيقية غير معروفة قبل تأكيد الدفع.

### التسوية تتم على settlement_net_amount (وليس net_amount القديم)

منذ v1.6.0، عند الضغط على "تسوية مع المشترك" في لوحة الإدارة، Modal التأكيد يعرض: **`settlement_net_amount`** (الصافي بالشيكل) الذي سيُحوَّل فعلياً، **`settlement_platform_fee`** (عمولة بوابة الدفع بالشيكل) المخصومة، وإن احتوت `metadata` على `settlement_fee_rate`/`settlement_fixed_fee` يُعرَض تفصيل الاحتساب أيضاً (مثال: "نسبة 2.50% + عمولة ثابتة 1.00 ILS") — وإن كان `settlement_source = togo_response` يُذكَر أنه مبلغ فعلي من البوابة، وإن كان `admin_manual` يُذكَر أنه أُدخل يدوياً. الإجراء نفسه لا يُعيد حساب أو يُعدِّل `settlement_amount`/`settlement_platform_fee`/`settlement_net_amount`؛ فقط `status → settled` و`settled_at → now()` — القيم المالية تُحسب مرة واحدة فقط (عند التحصيل، أو عند تأكيد الأدمن اليدوي)، ولا تتغيّر بعد ذلك حتى لو عدّل الأدمن إعدادات العمولة لاحقاً. **الزر لا يظهر إطلاقاً إن كانت `settlement_net_amount = NULL`** (راجع `PaymentCollection::isReadyForSettlement()`).

### ⚠️ إصلاح: طلب تسوية عالق على "قيد المراجعة" رغم تسوية تحصيلاته (v1.7.1)

**السبب الجذري:** زر "تسوية مع المشترك" المستقل في `PaymentCollectionResource` (موجود منذ v1.6.0، قبل وجود `SettlementRequest`) كان بلا أي وعي بطلبات التسوية — يُمكن استخدامه مباشرة على `PaymentCollection` مرتبط بطلب تسوية **مفتوح** (pending/approved)، فيُحوِّل `status → settled` دون أن يمسّ `SettlementRequest` المرتبط إطلاقاً. النتيجة: كل تحصيلات الطلب تصبح `settled` لكن الطلب نفسه يبقى عالقاً على `pending`/`approved` للأبد، لأن المسار الوحيد الذي يُحدِّث `SettlementRequest.status → paid` هو "تعليم كمدفوع" في `SettlementRequestResource`، ولم يُستخدَم.

**الإصلاح (منع التكرار):**
- `PaymentCollection::hasOpenSettlementRequest()` — يفحص إن كان التحصيل مرتبطاً بطلب `pending`/`approved`.
- زر "تسوية مع المشترك" في `PaymentCollectionResource` أصبح **معطّلاً (`disabled`) مع tooltip توضيحي** (وليس مخفياً) عندما `hasOpenSettlementRequest() = true` — يوجّه الأدمن لاستخدام "تعليم كمدفوع" بدلاً من ذلك.

**إصلاح الطلبات القديمة المتأثرة:**
```
php artisan settlement-requests:backfill-paid --dry-run   # عرض فقط
php artisan settlement-requests:backfill-paid              # تنفيذ فعلي
```
يبحث عن أي `SettlementRequest` بحالة غير `paid` وكل تحصيلاته `settled`، ويُحدِّث فقط `status → paid` و`paid_at → أحدث settled_at` بين تحصيلاته — لا يلمس `PaymentCollection` ولا `Invoice` ولا ينشئ `Transaction`.

---

## منع الدفع المكرر (v1.1.0)

### مستوى الفاتورة الواحدة (سجل تحصيل واحد)
- `payment_collections.invoice_id` أصبح **unique** (migration `2026_07_01_000002`).
- نفس الـ migration تُنظِّف تلقائياً أي تكرارات `invoice_id` موجودة مسبقاً **قبل** إضافة الـ unique index (دالة `cleanupDuplicateCollections()` في الـ migration نفسها) — بالأفضلية: `status = collected` → أحدث `collected_at` → أحدث `updated_at` → أحدث `id`. تُسجَّل كل عملية حذف في `storage/logs/laravel.log`. لا تُغيّر أي `Invoice`/`Transaction` ولا تحذفها — فقط صفوف `payment_collections` الزائدة. هذا يجعل `php artisan migrate` آمناً للتشغيل المباشر حتى لو وُجدت بيانات اختبار قديمة مكرَّرة.
- `checkout()` يستخدم `PaymentCollection::firstOrCreate(['invoice_id' => ...], [...])` بدل `create()` — سجل واحد يُعاد استخدامه وتحديثه في كل محاولة (retry بعد فشل/إلغاء)، لا تراكم سجلات.
- لأن `firstOrCreate` وحدها لا تمنع Race Condition بالكامل (نافذة زمنية بين `first()` و`create()`)، الـ unique index هو خط الدفاع الحقيقي؛ `checkout()` يمسك `QueryException` عند تجاوز طلبين متزامنين للفحص، ويُعيد قراءة السجل الذي فاز بدل إفشال الطلب.
- حماية إضافية: إن كان `PaymentCollection.status` بالفعل `collected` أو `settled`، يُرفض فتح checkout جديد فوراً.

### مستوى الـ callback (منع تنفيذ markPaid مرتين)
- فحص سريع خارج أي I/O: لو `invoice.status === paid` أو `collection.status` بالفعل `collected/settled` → رجوع فوري بلا أي تحديث.
- التحقق الفعلي من Togo (`verifyOrder`) يبقى **خارج** أي معاملة قافلة — استدعاء شبكي بطيء لا يجب أن يُبقي قفل صف مفتوحاً.
- عند نجاح التحقق فقط: `DB::transaction` تُقفل صف الفاتورة بـ `lockForUpdate()` وتُعيد فحص حالته **داخل القفل** قبل تنفيذ أي شيء. لو وصل طلبا callback متزامنان لنفس الفاتورة (مثلاً تبويبان مفتوحان على رابط العودة)، الثاني يجد الفاتورة `paid` بعد أن يُحرَّر القفل من الأول ويتوقف دون تنفيذ `markPaid` أو تحديث `PaymentCollection` مرة ثانية.
- النتيجة: **معاملة دخل واحدة فقط** و**PaymentCollection واحد بحالة collected واحدة** لكل فاتورة، حتى مع طلبات متزامنة.

---

## لوحة الإدارة (Filament) — PaymentCollectionResource

مسار: `/admin/payment-collections` (مجموعة تنقّل "المدفوعات"، بجانب طلبات الدفع).

**الملفات:**
- `app/Filament/Resources/PaymentCollectionResource.php`
- `app/Filament/Resources/PaymentCollectionResource/Pages/ListPaymentCollections.php`

**الأعمدة:** رقم الفاتورة (رابط لصفحة الفاتورة في تبويب جديد)، المشترك (+ بريده كوصف)، العميل الدافع، المزود، **مبلغ الفاتورة** (`amount` + `currency`)، **مبلغ التسوية** (`settlement_amount` + `settlement_currency` — "غير معروف بعد" إن كان `NULL`)، **عمولة التسوية** (`settlement_platform_fee`)، **صافي التسوية** (`settlement_net_amount` — نص تحذيري أصفر "⚠️ بانتظار تحديد مبلغ التسوية" إن كان `NULL`)، الحالة (Badge ملوّن عبر `PaymentCollectionStatus`: `pending`=warning، `collected`=success، `settled`=info، `failed`=danger، `refunded`=gray)، تاريخ التحصيل، تاريخ التسوية. أعمدة `platform_fee`/`net_amount` القديمة (بعملة الفاتورة) لم تعد تظهر في الجدول (تبقى في DB للتوافق التاريخي فقط).

**Filter:** حسب الحالة (`SelectFilter` على `status`، الخيارات من `PaymentCollectionStatus::cases()`).

**Tabs:** الكل / محصَّلة (`collected`) / تمت تسويتها (`settled`) / فاشلة (`failed`) / مستردة (`refunded`) / **بانتظار تحديد مبلغ التسوية** (`collected` و`settlement_amount IS NULL`، لون badge أصفر `warning` — جديد v1.7.0) — كل تبويب يعرض عدّاداً (badge) بعدد السجلات.

**تنبيه الأدمن (v1.7.0):** `AwaitingSettlementAmountWidget` (`app/Filament/Resources/PaymentCollectionResource/Widgets/`) — بطاقة إحصائية أعلى صفحة القائمة تعرض "تحصيلات تحتاج تحديد مبلغ التسوية" بنفس عدد تبويب "بانتظار تحديد مبلغ التسوية"، بلون `warning` إن كان العدد > 0. النقر عليها يفتح `/admin/payment-collections?activeTab=awaiting_settlement`. مُسجَّلة فقط داخل `ListPaymentCollections::getHeaderWidgets()` — **ليست** في `app/Filament/Widgets/` (الذي يخضع لاكتشاف تلقائي `discoverWidgets()` ويظهر على الداشبورد الرئيسي) حتى تبقى مرتبطة بصفحة PaymentCollectionResource فقط.

**Action — "تحديد مبلغ التسوية يدوياً"** (جديد v1.6.0):
- تظهر فقط عندما `status === Collected` **و** `settlement_amount === NULL` (فاتورة بعملة أجنبية بانتظار تحديد المبلغ).
- Modal بحقلين: مبلغ التسوية بالشيكل (إلزامي) وسعر الصرف (اختياري، للتوثيق فقط).
- عند الحفظ: تُحسَب `settlement_platform_fee`/`settlement_net_amount` تلقائياً بنفس صيغة العمولة (إعدادات لوحة الإدارة)، وتُحفَظ `settlement_source = admin_manual` في `metadata`.

**Action — "تسوية مع المشترك":**
- تظهر فقط عندما `status === Collected` **و** `settlement_net_amount !== NULL` (أي `PaymentCollection::isReadyForSettlement()`).
- تطلب تأكيداً (Modal) يوضّح الصافي بالشيكل (`settlement_net_amount`) والمشترك المستفيد.
- عند التأكيد: تُحدِّث `status → settled` و`settled_at → now()` **فقط**. لا تُعدِّل `Invoice` ولا تُنشئ `Transaction` ولا تُغيّر أي عمود `settlement_*` — هذا إجراء تسجيل يدوي لتحويل تم خارج النظام، وليس تنفيذاً فعلياً لأي تحويل مالي.

**الحذف:** ممنوع بالكامل — `canDelete()`/`canDeleteAny()` تُعيدان `false`، ولا يوجد أي `DeleteAction`/`DeleteBulkAction` في الجدول. لا إنشاء ولا تعديل يدوي أيضاً (`canCreate()`/`canEdit()` = `false`) — السجلات تُدار فقط عبر `InvoicePaymentController` (التحصيل) والإجراءين أعلاه.

---

## واجهة المشترك — تحصيلاتي (v1.5.0، مُحدَّثة بالشيكل في v1.6.0، بزر طلب تسوية في v1.7.0)

صفحة **للقراءة فقط** يراها كل مشترك داخل دراهم لمتابعة عمليات تحصيل فواتيره عبر بوابة الدفع — دون أي إمكانية للتسوية أو التعديل أو الحذف من هنا (هذه العمليات تبقى حصراً من لوحة إدارة Filament، راجع القسم السابق).

**المسار:** `GET /collections` (اسم الراوت `collections.index`) — ضمن مجموعة `middleware(['auth', 'verified'])`، بجانب مسارات `transactions`.

**الملفات:**
- `app/Http/Controllers/CollectionController.php` — `index()` فقط
- `resources/views/collections/index.blade.php`
- رابط في القائمة الجانبية "تحصيلاتي" (`resources/views/layouts/partials/sidebar.blade.php`، قسم "المالية"، بعد "المعاملات")

**عزل البيانات (Multi-tenancy):** الاستعلام الأساسي `PaymentCollection::where('user_id', auth()->id())` — لا يرى أي مشترك سجلات مشترك آخر إطلاقاً؛ هذا امتداد طبيعي لقاعدة العزل المتبعة في كل مسارات `auth` بالمشروع (الاستثناء الوحيد يبقى رابط الدفع العام `/pay/*`).

**البطاقات الإحصائية — بالشيكل حصراً منذ v1.6.0** (تُحسب دائماً على كامل سجلات المستخدم بعملة تسوية `ILS`، بمعزل عن فلتر الجدول؛ الاستعلامات تُقيَّد صراحةً بـ `settlement_currency = 'ILS'` حتى لا تُخلَط أي عملة تسوية أخرى مستقبلاً في نفس المجموع):

| البطاقة | الحساب | الشرط |
|---|---|---|
| إجمالي المحصّل للتسوية | `SUM(settlement_amount)` | `status = collected` و`settlement_amount` معروف |
| الصافي بانتظار التسوية | `SUM(settlement_net_amount)` | `status = collected` و`settlement_net_amount` معروف |
| إجمالي العمولة المخصومة | `SUM(settlement_platform_fee)` | `status IN (collected, settled)` |
| تمت تسويته معي | `SUM(settlement_net_amount)` | `status = settled` |

سجلات `status = collected` التي لا يزال `settlement_amount` فيها `NULL` (فواتير بعملة أجنبية بانتظار تحديد الأدمن للمبلغ) **لا تدخل في أي من المجاميع أعلاه** — بدلاً من ذلك تظهر رسالة تنبيه منفصلة أعلى الصفحة توضّح عددها بالضبط (`$pendingSettlementCount` في `CollectionController`)، حتى لا تختفي هذه المبالغ بصمت من نظر المشترك.

**الجدول:** رقم الفاتورة (رابط لـ `invoices.show`) · العميل · **مبلغ الفاتورة الأصلي** (`amount` + `currency` — عملة الفاتورة كما هي) · **صافي التسوية (بالشيكل)** (`settlement_net_amount` + `settlement_currency`، أو نص تحذيري "بانتظار تحديد المبلغ" إن كان `NULL` والحالة `collected`) · الحالة (Badge بنفس `PaymentCollectionStatus::badgeClass()`/`label()`/`icon()`) · تاريخ التحصيل · تاريخ التسوية. عمودا العمولة والمبلغ بعملة الفاتورة القديمين حُذفا من هذا الجدول (استُبدِلا بعمود صافي التسوية بالشيكل).

**الفلتر:** حسب الحالة — الكل / `pending` / `collected` / `settled` / `failed` / `refunded`، عبر `<x-filter-bar>` (GET، نفس نمط صفحة المعاملات).

**زر "طلب تسوية" (v1.7.0):** يظهر أعلى الصفحة (داخل `<x-page-header>`) فقط عندما توجد مبالغ جاهزة فعلاً (`PaymentCollection::eligibleForSettlementRequest()` — راجع القسم التالي "طلبات التسوية") **ولا** يوجد طلب `pending` مفتوح بالفعل للمستخدم. عند الضغط: `POST /settlement-requests` (`settlement-requests.store`) — **لا تحويل مال، فقط إنشاء طلب**. إن كان لدى المستخدم طلب `pending` بالفعل، يظهر شريط أزرق بدلاً من الزر بدل إخفاء أي رسالة بصمت.

**قسم "طلبات التسوية" (v1.7.0):** أسفل جدول التحصيلات — يعرض آخر 10 طلبات تسوية للمستخدم: الرقم (`#id`)، المبلغ، الحالة (Badge)، تاريخ الطلب، تاريخ الدفع. عند الرفض يُعرَض `admin_notes` (سبب الرفض) تحت الـ Badge مباشرة.

**قيود مقصودة:**
- **لا زر تسوية هنا إطلاقاً** — التسوية الفعلية (تحديث `PaymentCollection.status = settled`) تبقى حصراً من `SettlementRequestResource`/`PaymentCollectionResource` في Filament (صلاحية الأدمن فقط). زر "طلب تسوية" هنا **لا يُغيّر أي status ولا يُحوِّل مالاً** — فقط يُنشئ طلباً للمراجعة.
- **لا تعديل ولا حذف** — لا يوجد أي راوت `PUT`/`PATCH`/`DELETE` لهذا المورد من جهة المشترك؛ الصفحة عرض فقط (`index()` هي الميثود الوحيدة في `CollectionController`، و`store()` في `SettlementRequestController` هي الوحيدة الأخرى المتاحة للمشترك، وهي إنشاء فقط).

---

## طلبات التسوية (SettlementRequest) — v1.7.0

المشترك **يطلب فقط** — لا يحدث أي تحويل مال تلقائي عند إنشاء الطلب، ولا حتى عند اعتماده. **الأدمن هو من يعتمد الطلب ثم يدفعه يدوياً خارج النظام**، وعندها فقط (عند "تعليم كمدفوع") تتحوّل كل `PaymentCollection` المرتبطة إلى `status = settled`.

### التدفق الكامل

```
المشترك في /collections يضغط "طلب تسوية" (يظهر فقط إن وُجدت مبالغ جاهزة)
  → POST /settlement-requests → SettlementRequestController::store()
      - يرفض إن كان لدى المستخدم طلب pending بالفعل (رسالة: "لديك طلب تسوية قيد المراجعة بالفعل.")
      - يجلب كل PaymentCollection المؤهَّلة (status=collected + settlement_net_amount غير null
        + غير مرتبطة بأي طلب "مفتوح" آخر — pending أو approved)
      - total_amount = SUM(settlement_net_amount)، currency = ILS دائماً
      - ينشئ SettlementRequest (status=pending) ويربط كل التحصيلات المؤهَّلة به (pivot)
      - لا يُغيّر status أو settled_at لأي PaymentCollection في هذه الخطوة

الأدمن في /admin/settlement-requests يراجع الطلب (مع قائمة التحصيلات المرتبطة بالضبط
عبر RelationManager) ثم:
  → "اعتماد الطلب"  → status=approved, reviewed_at=now()   (لا تحويل مال بعد)
  → "رفض الطلب"     → status=rejected, reviewed_at=now(), admin_notes=سبب الرفض (إلزامي)
                       (التحصيلات تبقى مؤهَّلة لطلب لاحق)

بعد التحويل الفعلي خارج النظام (طلب معتمد فقط):
  → "تعليم كمدفوع"  → status=paid, paid_at=now()
                       + كل PaymentCollection المرتبطة (لا تزال collected) → status=settled, settled_at=now()
                       لا Transaction جديدة، لا تعديل على أي Invoice
```

### لماذا `belongsToMany` لا `hasMany`

نفس `PaymentCollection` قد يظهر في أكثر من `SettlementRequest` عبر الزمن — مثلاً إن رُفض طلب أول، يبقى التحصيل مؤهَّلاً (`status` لم يتغيّر) ويمكن إدراجه في طلب لاحق. جدول pivot `settlement_request_payment_collection` (بدون عمود `id` منفصل — مفتاح أساسي مركّب على العمودين) يسجّل كل ربط تاريخياً.

### منع ازدواج طلب المبلغ نفسه في طلبين مفتوحين

`PaymentCollection::scopeEligibleForSettlementRequest()` يستبعد أي تحصيل مرتبط حالياً بطلب `pending` أو `approved` (طلب "مفتوح" لم يُدفع أو يُرفض بعد) — هذا يمنع احتساب نفس `settlement_net_amount` مرتين ضمن طلبين متزامنين، حتى لو سمحت قاعدة "منع التكرار" (أدناه) بإنشاء طلب ثانٍ بينما الأول لا يزال `approved` غير مدفوع.

### منع تكرار الطلبات (Best-effort)

`SettlementRequestController::store()` يرفض الطلب فوراً إن وُجد طلب `pending` للمستخدم (رسالة: "لديك طلب تسوية قيد المراجعة بالفعل.")، مع إعادة فحص ثانية داخل `DB::transaction` (نافذة أضيق لنقرتين متزامنتين على الزر). ⚠️ هذا حماية **متناسبة** مع طبيعة الإجراء (نقرة يدوية نادرة من مستخدم واحد على صفحته الخاصة) — على عكس `payment_collections.invoice_id` (فريد على مستوى DB لأنه يحمي من Race Condition في مسار دفع عام بلا Auth)، لا يوجد فهرس DB فريد جزئي يمنع أكثر من طلب `pending` واحد لكل مستخدم (MySQL لا يدعم Partial Unique Index بسهولة)؛ الاعتماد هنا على الفحص المزدوج + Log عند أي شذوذ.

### واجهة الأدمن — SettlementRequestResource

مسار: `/admin/settlement-requests` (مجموعة تنقّل "المدفوعات"، بجانب `PaymentCollectionResource`).

**الأعمدة:** الرقم (`#id`)، المشترك (+ بريده)، المبلغ الإجمالي، عدد التحصيلات المرتبطة، الحالة (Badge: `pending`=warning، `approved`=info، `rejected`=danger، `paid`=success)، تاريخ الطلب، تاريخ المراجعة، تاريخ الدفع، ملاحظات الأدمن (مخفي افتراضياً).

**Tabs:** الكل / قيد المراجعة (`pending`) / مُعتمَدة (`approved`) / مدفوعة (`paid`) / مرفوضة (`rejected`).

**RelationManager — "التحصيلات المرتبطة":** جدول للقراءة فقط أسفل صفحة كل طلب، يعرض بالضبط أي `PaymentCollection` شكّلت `total_amount` (رقم الفاتورة، العميل، مبلغ الفاتورة، صافي التسوية، الحالة) — ضروري ليراجع الأدمن التفاصيل قبل الاعتماد، لا إضافة/فصل/تعديل من هنا.

**Actions:**
1. **"اعتماد الطلب"** — تظهر فقط عند `status=pending`. تُحدِّث `status→approved`, `reviewed_at→now()` فقط. لا تحويل مال.
2. **"رفض الطلب"** — تظهر فقط عند `status=pending`. Modal بحقل `admin_notes` **إلزامي** (سبب الرفض). تُحدِّث `status→rejected`, `reviewed_at→now()`, `admin_notes`.
3. **"تعليم كمدفوع"** — تظهر فقط عند `status=approved`. `DB::transaction` تُنفِّذ معاً: `SettlementRequest.status→paid` + `paid_at→now()`، وكل `PaymentCollection` المرتبطة (لا تزال `collected`) → `status→settled` + `settled_at→now()`. **لا تُنشئ Transaction ولا تُعدِّل أي Invoice** — تماماً كإجراء "تسوية مع المشترك" الفردي في `PaymentCollectionResource`، لكن بالجملة لكل تحصيلات الطلب معاً.

**الحذف/الإنشاء/التعديل اليدوي:** ممنوعة بالكامل (`canCreate()`/`canEdit()`/`canDelete()`/`canDeleteAny()` = `false`) — الطلبات تُنشأ فقط عبر `SettlementRequestController::store()` من المشترك.

---

## القيود الحالية / أعمال مستقبلية

- **عمولة Togo الفعلية غير مؤكدة رسمياً** — `extractCommissionAmount()`/`extractSettlementAmount()`/`extractExchangeRate()` تخمين "قدر المستطاع" لحقول محتملة؛ إلى أن تُؤكِّد Togo الحقول الرسمية، الاعتماد الأساسي عملياً على إعدادات العمولة في لوحة الإدارة + التحديد اليدوي لمبلغ التسوية عند الحاجة (راجع `metadata.settlement_source` لكل سجل لمعرفة أيهما استُخدم فعلياً).
- **فواتير العملة الأجنبية تحتاج مراجعة يدوية إن لم تُرجِع Togo مبلغ/سعر صرف** — تبقى `status = collected` (المبلغ محصَّل فعلياً ولدى دراهم) لكن `settlement_net_amount = NULL` يمنع زر "تسوية مع المشترك" حتى يستخدم الأدمن "تحديد مبلغ التسوية يدوياً". لا حد زمني تلقائي أو تنبيه دوري لهذه الحالة حالياً (تحسين مستقبلي محتمل: تقرير/تذكير للأدمن بالسجلات المعلَّقة).
- **يعتمد على نفس قيود Togo الحالية** (راجع `docs/TOGO-PAYMENT-GATEWAY.md`) — ASCII فقط في بعض الحقول، لا Webhooks (Redirect فقط)، ومشكلة `receiver_address_id` القائمة مع بيانات عربية (راجع ملاحظة المشروع في الذاكرة).
- **لا payouts تلقائية** — لا يوجد أي كود يُحوِّل أموالاً فعلياً للمشترك؛ هذا قرار متعمّد لهذا الإصدار.
- **`platform_fee`/`net_amount` (بعملة الفاتورة) لا تزالان تُحسبان وتُحفظان** في كل تحصيل جديد للتوافق التاريخي، لكنهما لم تعودا تُعرَضان في أي واجهة (لا لوحة الإدارة ولا صفحة المشترك) — المصدر الوحيد المُعتمَد الآن هو `settlement_*`.
- **منع تكرار طلبات التسوية best-effort لا DB-level** — راجع "منع تكرار الطلبات (Best-effort)" أعلاه؛ كافٍ للاستخدام اليدوي العادي لكنه ليس بصرامة الحماية المطبَّقة على مسار الدفع العام (`payment_collections.invoice_id` الفريد).
- **لا حد أقصى/تحقق على مبلغ `SettlementRequest` الأدنى** — أي مبلغ إجمالي > 0 يُنشئ طلباً صالحاً، حتى لو كان صغيراً جداً؛ تحسين مستقبلي محتمل: حد أدنى قابل للتهيئة من لوحة الإدارة.
