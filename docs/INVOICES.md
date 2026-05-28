# 🧾 نظام الفواتير — Invoice System

> دراهم SaaS — Laravel 12 / PHP 8.2  
> آخر تحديث: 28 مايو 2026 — Phase 18 ✅ مكتمل

---

## 📋 نظرة عامة

نظام فواتير متكامل مضمّن داخل منصة **دراهم** يتيح للمستقلين وأصحاب الأعمال إنشاء فواتير احترافية مرتبطة بالعملاء والمشاريع، مع دعم بنود متعددة، ضريبة القيمة المضافة، الخصومات، وتصدير PDF مباشرة من المتصفح.

---

## 🗂️ هيكل الملفات

```
app/
├── Models/
│   ├── Invoice.php               # النموذج الرئيسي + SoftDeletes
│   └── InvoiceItem.php           # بنود الفاتورة
├── Support/Enums/
│   └── InvoiceStatus.php         # Enum: draft | sent | paid | overdue | cancelled
├── Http/Controllers/
│   └── InvoiceController.php     # CRUD كامل + إجراءات الحالة

database/migrations/
└── 2026_05_27_100000_create_invoices_table.php   # جدولا invoices + invoice_items

resources/views/invoices/
├── index.blade.php               # قائمة الفواتير
├── create.blade.php              # فورم الإنشاء (Alpine.js)
├── edit.blade.php                # فورم التعديل (Alpine.js)
└── show.blade.php                # عرض الفاتورة + طباعة PDF

routes/web.php                    # مجموعة routes invoices.*
```

---

## 🗄️ قاعدة البيانات

### جدول `invoices`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | `bigIncrements` | المفتاح الرئيسي |
| `ulid` | `varchar(26)` unique | معرّف خارجي آمن — يُستخدم في الـ URL |
| `user_id` | `FK → users.id` | المستخدم المالك |
| `client_id` | `FK → clients.id` | العميل المرتبط |
| `project_id` | `char(26) nullable → projects.id` | المشروع (اختياري) — char لتوافق ULID |
| `number` | `varchar(50)` unique | رقم الفاتورة (INV-0001، INV-0002…) |
| `status` | `varchar(20)` default `draft` | الحالة (يُخزَّن كـ string، يُقرأ كـ Enum) |
| `title` | `varchar(255)` nullable | عنوان وصفي اختياري |
| `issue_date` | `date` | تاريخ الإصدار |
| `due_date` | `date` nullable | تاريخ الاستحقاق |
| `subtotal` | `decimal(12,2)` | المجموع قبل الضريبة |
| `tax_rate` | `decimal(5,2)` | نسبة الضريبة % |
| `tax_amount` | `decimal(12,2)` | قيمة الضريبة المحسوبة |
| `discount` | `decimal(12,2)` | خصم بالقيمة (ليس نسبة) |
| `total` | `decimal(12,2)` | الإجمالي النهائي |
| `currency` | `varchar(3)` default `ILS` | رمز العملة (ILS / USD / EUR / JOD) |
| `notes` | `text` nullable | ملاحظات للعميل |
| `terms` | `text` nullable | الشروط والأحكام |
| `sent_at` | `timestamp` nullable | وقت تغيير الحالة إلى مُرسَلة |
| `paid_at` | `timestamp` nullable | وقت تسجيل الدفع |
| `deleted_at` | `timestamp` nullable | SoftDelete |

> **ملاحظة FK:** `project_id` يجب أن يكون `char(26)` لأن `projects.id` هو `ulid` وليس `bigInteger`.

### جدول `invoice_items`

| العمود | النوع | الوصف |
|--------|-------|-------|
| `id` | `bigIncrements` | |
| `invoice_id` | `FK → invoices.id` cascadeDelete | |
| `description` | `varchar(255)` | وصف الخدمة أو المنتج |
| `quantity` | `decimal(10,2)` | الكمية |
| `unit_price` | `decimal(12,2)` | سعر الوحدة |
| `total` | `decimal(12,2)` | quantity × unit_price |
| `sort_order` | `smallInteger` | ترتيب العرض |

---

## 🔢 ترقيم الفواتير

يتم التوليد تلقائياً في `Invoice::boot()` عند الإنشاء:

```php
// INV-0001, INV-0002, ... — per user
$last = self::where('user_id', $userId)->max('id') ?? 0;
return 'INV-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
```

كل مستخدم له تسلسل مستقل لضمان خصوصية البيانات في بيئة Multi-tenant.

---

## 📊 InvoiceStatus Enum

**الملف:** `app/Support/Enums/InvoiceStatus.php`

| القيمة | التسمية | الأيقونة | الـ Badge Class |
|--------|---------|---------|----------------|
| `draft` | مسودة | 📝 | `bg-gray-100 text-gray-600` |
| `sent` | مُرسَلة | 📤 | `bg-blue-100 text-blue-700` |
| `paid` | مدفوعة | ✅ | `bg-teal-100 text-teal-700` |
| `overdue` | متأخرة | ⚠️ | `bg-red-100 text-red-700` |
| `cancelled` | ملغاة | ❌ | `bg-gray-100 text-gray-400` |

**دورة الحياة المسموح بها:**

```
draft ──► sent ──► paid
  └──────────────► cancelled
```

---

## 🔧 Invoice Model — الميزات الأساسية

```php
// توليد ULID + رقم الفاتورة تلقائياً عند الإنشاء
static::creating(function (self $invoice) {
    $invoice->ulid   = Str::ulid()->toString();
    $invoice->number = self::generateNumber($invoice->user_id);
});

// إعادة حساب الإجماليات بعد تعديل البنود
$invoice->recalculate();
// يحسب: subtotal → tax_amount → total (مع خصم)

// التحقق من التأخر
$invoice->isOverdue();
// true إذا: due_date مضت + الحالة ليست paid/cancelled

// Route model binding عبر ULID (لا عبر id)
getRouteKeyName() → 'ulid'
```

---

## 🛣️ Routes

```php
Route::prefix('invoices')->name('invoices.')->group(function () {
    GET    /invoices              → index       (قائمة كل الفواتير)
    GET    /invoices/create       → create      (فورم إنشاء)
    POST   /invoices              → store       (حفظ فاتورة جديدة)
    GET    /invoices/{ulid}       → show        (عرض فاتورة)
    GET    /invoices/{ulid}/edit  → edit        (فورم تعديل)
    PUT    /invoices/{ulid}       → update      (حفظ التعديلات)
    DELETE /invoices/{ulid}       → destroy     (حذف ناعم)
    POST   /invoices/{ulid}/mark-sent  → markSent   (→ sent)
    POST   /invoices/{ulid}/mark-paid  → markPaid   (→ paid)
    POST   /invoices/{ulid}/cancel     → cancel     (→ cancelled)
});
```

جميع الروutes محمية بـ `middleware(['auth', 'verified'])`.

---

## 🎛️ InvoiceController — الإجراءات

### إنشاء فاتورة (`store`)

```
1. التحقق من صحة البيانات (client_id, items[], currency, ...)
2. التحقق من ملكية العميل (client.user_id = auth.id)
3. إنشاء سجل Invoice (status = draft تلقائياً)
4. إنشاء InvoiceItems لكل بند
5. استدعاء recalculate() لحساب الإجماليات
6. Redirect إلى صفحة العرض مع رسالة نجاح
```

### تحديث فاتورة (`update`)

```
1. التحقق من الملكية عبر ulid
2. التحقق من البيانات
3. تحديث حقول الفاتورة
4. حذف البنود القديمة وإعادة إنشائها
5. recalculate()
```

> **ملاحظة أمان:** التعديل مسموح فقط على فواتير بحالة `draft`. الزر يختفي في الواجهة لغير المسودات.

### إجراءات الحالة

```php
markSent($ulid)   → status = sent,      sent_at = now()
markPaid($ulid)   → status = paid,      paid_at = now()
cancel($ulid)     → status = cancelled
destroy($ulid)    → SoftDelete → redirect clients.show
```

---

## 🖥️ الواجهات

### قائمة الفواتير — `invoices/index`

- إحصائيات سريعة: إجمالي / مسودة+مُرسَلة / مدفوعة / متأخرة
- جدول بالأعمدة: رقم الفاتورة، العميل، المشروع، الحالة، تاريخ الإصدار، الاستحقاق، الإجمالي
- Badge حمراء للفواتير المتأخرة
- Pagination (20 لكل صفحة)

### فورم الإنشاء والتعديل — Alpine.js

```javascript
invoiceForm() {
    items: [{ description, quantity, unit_price }],
    taxRate, discount,
    // Live calculation:
    recalc() {
        subtotal  = Σ(quantity × unit_price)
        taxAmount = subtotal × (taxRate / 100)
        total     = max(0, subtotal + taxAmount - discount)
    }
}
```

- إضافة / حذف بنود ديناميكياً
- عرض الإجماليات يتحدث فوراً مع كل تغيير

### صفحة العرض — `invoices/show`

- ورقة فاتورة احترافية مع: رأس، بيانات العميل، جدول البنود، الإجماليات، ملاحظات
- طباعة مباشرة / تصدير PDF عبر `window.print()`
- شريط إجراءات: تحديد كمُرسَلة، تسجيل الدفع، طباعة، تعديل، إلغاء
- ختم "مدفوعة" شفاف يظهر على الفاتورة عند الدفع

### CSS الطباعة

```css
@media print {
    nav, header, .print\:hidden { display: none !important; }
    body { background: white; }
}
```

---

## 🔗 التكامل مع ملف العميل

تظهر فواتير العميل في تبويب **🧾 الفواتير** داخل `crm/clients/show`:

```php
// CRM ClientController::show()
$clientInvoices = Invoice::where('client_id', $client->id)
    ->where('user_id', $request->user()->id)
    ->with('project')
    ->orderByDesc('created_at')
    ->get();
```

- Badge بعدد الفواتير على رأس التبويب
- بطاقة لكل فاتورة: رقم، حالة، تاريخ، مشروع، إجمالي
- زر "إنشاء فاتورة" يمرر `client_id` تلقائياً

---

## 🔗 التكامل مع المشاريع

### الربط اليدوي (عند إنشاء الفاتورة)

عند اختيار مشروع يدوياً في فورم الفاتورة:
- يظهر اسم المشروع في ورقة الفاتورة
- يُعرض في قائمة الفواتير
- يُستخدم للتقارير المستقبلية

### الإنشاء التلقائي عند إنشاء مشروع جديد ⚡

**الملف:** `ProjectController::store()` → استدعاء `createDraftInvoice()`

عند إنشاء مشروع مرتبط بعميل، تُنشأ فاتورة مسودة تلقائياً:

```
1. تاريخ الإصدار = اليوم
2. تاريخ الاستحقاق = اليوم + 30 يوم
3. العملة = عملة المشروع
4. العنوان = اسم المشروع
5. البنود:
   - إن وُجدت خدمات → بند لكل خدمة (الاسم + المبلغ)
   - إن لم توجد خدمات + يوجد contract_value → بند واحد بقيمة العقد
   - إن لم يوجد شيء → فاتورة فارغة (يعبؤها المستخدم لاحقاً)
6. تُحسب الإجماليات تلقائياً (recalculate())
```

الفاتورة تبقى **مسودة** حتى يراجعها المستخدم ويرسلها للعميل.

> **قيد تقني:** `project_id` من نوع `char(26)` في جدول `invoices` لأن `projects.id` هو `ulid` وليس `bigInteger`. هذا الاستثناء موثَّق لتجنب أخطاء المايجريشن مستقبلاً.

---

## 🔒 الأمان والعزل

- كل استعلام يشترط `user_id = auth()->id()` — لا يمكن لمستخدم رؤية فواتير مستخدم آخر
- التحقق من ملكية العميل قبل إنشاء الفاتورة
- حذف ناعم (SoftDeletes) — الفواتير المحذوفة قابلة للاسترداد من DB
- Route model binding عبر `ulid` لا `id` — يمنع تخمين الأرقام التسلسلية

---

## 📌 القرارات التقنية

| القرار | السبب |
|--------|-------|
| ULID كمعرّف خارجي | أمان (لا يمكن تخمين الأرقام)، مناسب للـ URL |
| SoftDeletes | الفواتير وثائق مالية لا تُحذف نهائياً |
| `char(26)` لـ project_id | توافق مع `projects.id` الذي هو ULID |
| recalculate() في PHP | ضمان دقة الأرقام بعد كل تعديل |
| Alpine.js للحساب | استجابة فورية بدون طلبات للسيرفر |
| `window.print()` للـ PDF | بدون مكتبات خارجية — المتصفح يتولى الأمر |
| إنشاء تلقائي للفاتورة مع المشروع | تقليل الخطوات على المستخدم — الفاتورة جاهزة مباشرةً |
| `$txData` بدون `project_id` عند null | إرسال null صراحةً يُلغي DEFAULT في MySQL — الحذف يتركها للـ DB |

---

## 🐛 إصلاحات موثَّقة

### 1 — `SQLSTATE 3780`: نوع FK غير متوافق

**التاريخ:** مايو 2026  
**المشكلة:** Migration أنشأت `invoices.project_id` كـ `unsignedBigInteger` بينما `projects.id` هو `char(26)` (ULID).  
**الإصلاح:** تغيير نوع العمود في المايجريشن:
```php
// BEFORE
$table->unsignedBigInteger('project_id')->nullable();
// AFTER
$table->char('project_id', 26)->nullable();
```

### 2 — `SQLSTATE 1364`: `project_id doesn't have a default value`

**التاريخ:** 28 مايو 2026  
**المشكلة:** عمود `transactions.project_id` في قاعدة البيانات الفعلية كان `NOT NULL` بدون قيمة افتراضية (schema drift)، رغم أن المايجريشن الأصلية تحدد `->nullable()`.  
**الإصلاح:** مايجريشن تصحيح:
```
database/migrations/2026_05_28_000001_make_transactions_project_id_nullable.php
```
```php
$table->char('project_id', 26)->nullable()->change();
```

### 3 — `project_id = null` يُسبب `SQLSTATE 1048`

**التاريخ:** مايو 2026  
**المشكلة:** إرسال `null` صراحةً في INSERT يُلغي `DEFAULT NULL` في MySQL.  
**الإصلاح:** حذف المفتاح من المصفوفة عند عدم وجود مشروع:
```php
// في InvoiceController::markPaid()
if ($invoice->project_id) {
    $txData['project_id'] = $invoice->project_id;
}
```

---

## 🚀 تحسينات مستقبلية مقترحة

- [ ] إرسال الفاتورة عبر البريد الإلكتروني مباشرة للعميل
- [ ] قوالب فواتير قابلة للتخصيص (شعار + ألوان)
- [ ] تصدير PDF بجودة أعلى باستخدام `barryvdh/laravel-dompdf`
- [ ] تذكيرات تلقائية للفواتير المتأخرة
- [ ] دعم العملات المتعددة في فاتورة واحدة
- [ ] بوابة العميل لعرض وتحميل فواتيره
