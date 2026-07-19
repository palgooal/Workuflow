# 💰 دراهم — SaaS Financial Platform

> نظّم فلوسك ومشاريعك كلها من مكان واحد واعرف بالضبط أين يذهب ربحك.

---

## OpenAI Build Week 2026

### Darahum AI Financial Copilot — المساعد المالي الذكي في دراهم

دراهم منصة مالية عربية بالدرجة الأولى، مصممة لمساعدة المستقلين والمنشآت الصغيرة على إدارة معاملاتهم ومحافظهم ومشاريعهم وفواتيرهم وديونهم وتقاريرهم المالية.

- **المنتج القائم:** يوفر دراهم أدوات الإدارة المالية والمحاسبية الأساسية قبل Build Week.
- **إضافة Build Week:** يضيف المساعد المالي الذكي تحليلاً آمناً ومخصصاً للبيانات المالية المجمعة، ثم يقدم رؤى وتوجيهات عربية للقراءة فقط باستخدام GPT-5.6.

### مسار الاستخدام

1. يفتح المستخدم المسجل والمصرح له صفحة المساعد العربية.
2. يضغط على زر بدء التحليل؛ ولا يبدأ أي طلب تلقائياً عند فتح الصفحة.
3. يبني التطبيق لقطة مالية مجمعة ومعزولة للمستخدم الحالي فقط.
4. ينفذ محلل محلي قواعد مخاطر مالية حتمية، مع إبقاء كل عملة مستقلة.
5. يحول GPT-5.6 الأدلة المدعومة فقط إلى رؤى عربية منظمة.
6. تعرض واجهة RTL النتيجة كتوجيهات للقراءة فقط، دون تعديل أي سجل مالي.

### البنية

| المكوّن | المسؤولية |
|---------|-----------|
| `FinancialSnapshotService` | يجمع قيماً مالية رقمية مسموحاً بها ومفصولة حسب العملة، مع عزل صريح بواسطة المستخدم. |
| `FinancialRiskAnalyzer` | يحلل المجاميع محلياً بقواعد ثابتة دون قاعدة بيانات أو مصادقة ضمنية أو تحويل عملات. |
| `OpenAiCopilotService` | يرسل المدخلات المنقحة إلى Responses API ويطبق مخططاً صارماً والتحقق المحلي من المخرجات. |
| نقطة التحليل الآمنة | تنسق بناء اللقطة والتحليل وطلب OpenAI، ثم تعيد النتيجة العامة المنظمة فقط. |
| واجهة عربية RTL | تعرض حالات الصحة والرؤى والإجراءات والقيود والتنبيه بصورة متجاوبة وميسرة. |

### السلامة والخصوصية

- تبدأ كل استعلامات البيانات المالية من `user_id` الصريح للمستخدم المصادق عليه، بما فيها الفواتير والمحافظ المرتبطة بالتحويلات.
- لا تغادر الخادم إلا مجاميع رقمية مدرجة في قائمة سماح؛ ولا تُرسل أسماء أو ملاحظات أو جهات اتصال أو سجلات خام أو معرّفات أو أسرار.
- تستخدم طلبات OpenAI الخيار `store=false`، ولا يحفظ التطبيق اللقطة أو التحليل أو النتيجة أو معرّف استجابة المزود.
- لا ينشئ المساعد سجلات مالية ولا يعدلها ولا يحذفها، وتظل توصياته إرشادية وللقراءة فقط.
- تبقى العملات منفصلة دائماً ولا تُستنتج أسعار صرف.
- تُستبعد التحويلات بين عملات مختلفة عند غياب سعر صرف موثوق، ويضيف التطبيق قيداً واضحاً للمستخدم عند حدوث ذلك.

### تكامل OpenAI

- النموذج: `gpt-5.6`.
- الواجهة: OpenAI Responses API.
- المخرجات: strict Structured Outputs باستخدام JSON Schema، مع تحقق إضافي على الخادم.
- لا يستخدم التكامل أدوات أو ملفات أو محادثات أو ربطاً ذا حالة بين الاستجابات.
- يبقى مفتاح API على الخادم ولا يصل إلى المتصفح أو الاستجابة العامة.

### ضوابط الأمان

- المصادقة والتحقق من الحساب وحماية الحساب النشط.
- منع التحليل أثناء انتحال شخصية مستخدم آخر.
- تحديد المعدل إلى خمسة طلبات تحليل لكل مستخدم في الساعة.
- تحويل أخطاء الإعداد أو المزود أو الاستجابة غير الصالحة إلى رسالة عربية عامة وآمنة.

### ما بُني خلال Build Week

- لقطة مالية مجمعة ومعزولة.
- تحليل مخاطر مالي حتمي ومحلي.
- رؤى عربية منظمة عبر GPT-5.6.
- نقطة تحليل محمية ومحددة المعدل.
- واجهة عربية RTL متجاوبة.
- ضوابط لجودة المخرجات والأدلة والقيود المعلنة للمستخدم.

### الاختبارات

تغطي حزمة المساعد المالي الملفات التالية:

- `tests/Feature/AiCopilot/FinancialSnapshotIsolationTest.php`
- `tests/Unit/Modules/AiCopilot/FinancialRiskAnalyzerTest.php`
- `tests/Feature/AiCopilot/OpenAiCopilotServiceTest.php`
- `tests/Feature/AiCopilot/AiCopilotEndpointTest.php`
- `tests/Feature/AiCopilot/AiCopilotPageTest.php`

آخر نتيجة تحقق: **65 اختباراً ناجحاً و426 assertion**.

```bash
php artisan test tests/Feature/AiCopilot/FinancialSnapshotIsolationTest.php tests/Unit/Modules/AiCopilot/FinancialRiskAnalyzerTest.php tests/Feature/AiCopilot/OpenAiCopilotServiceTest.php tests/Feature/AiCopilot/AiCopilotEndpointTest.php tests/Feature/AiCopilot/AiCopilotPageTest.php
```

### إعداد OpenAI محلياً

أضف قيماً خاصة ببيئتك إلى `.env`، ولا تضع مفتاحاً حقيقياً في المستودع:

```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-5.6
OPENAI_TIMEOUT=30
OPENAI_MAX_OUTPUT_TOKENS=1800
```

بعد تعديل الإعدادات، امسح ذاكرة إعداد Laravel المؤقتة:

```bash
php artisan config:clear
```

### الحدود

- لا ينفذ المساعد تحويلات بأسعار الصرف.
- لا يقدم اتجاهات أو توقعات دون أدلة زمنية متسلسلة.
- الإرشادات تعليمية وللقراءة فقط.
- المخرجات ليست نصيحة محاسبية أو استثمارية أو ضريبية أو قانونية.

### العرض

- رابط العرض المباشر: _يُضاف لاحقاً._
- رابط فيديو العرض العام: _يُضاف لاحقاً._
- لقطات الشاشة: _تُضاف لاحقاً._

---

## 📚 وثائق المشروع

| الملف | الوصف |
|-------|-------|
| [📋 PROJECT.md](docs/PROJECT.md) | وصف المشروع — المشكلة، الحل، المميزات، الأهداف |
| [🏗️ ARCHITECTURE.md](docs/ARCHITECTURE.md) | الهيكل التقني — DB، Folder Structure، القرارات المعمارية |
| [✅ TASKS.md](docs/TASKS.md) | خطة المهام — 52 مهمة موزعة على 14 مرحلة |

> **قبل أي تطوير — اقرأ الوثائق أولاً.**

---

## 🚀 التقنيات

| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| PHP | `^8.2` | لغة الخادم |
| Laravel | `^12.0` | إطار عمل الخادم |
| Tailwind CSS | `^3.1.0` | التنسيق |
| Alpine.js | `^3.15.12` | تفاعلات الواجهة |
| Vite | `^7.0.7` | بناء الأصول |
| Laravel Vite Plugin | `^2.0.0` | تكامل Laravel مع Vite |

---

## ⚙️ متطلبات التشغيل

- PHP متوافق مع القيد `^8.2` المعلن في `composer.json`.
- Composer لتثبيت حزم PHP؛ لا يحدد المستودع حداً أدنى لإصداره.
- Node.js وnpm لتثبيت وبناء حزم الواجهة؛ لا يعلن `package.json` قيد `engines` لإصدار Node.js.
- قاعدة بيانات مضبوطة عبر إعدادات Laravel؛ لا يحدد ملفا الحزم حداً أدنى لإصدار محرك قاعدة البيانات.

---

## 🛠️ تثبيت المشروع

```bash
# 1. استنساخ المشروع
git clone REPOSITORY_URL
cd workuflow

# 2. تثبيت حزم PHP
composer install

# 3. تثبيت حزم Node
npm install

# 4. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 5. إعداد قاعدة البيانات
php artisan migrate --seed

# 6. تشغيل المشروع
php artisan serve
npm run dev
```

---

## 🗂️ هيكل المجلدات الرئيسي

```
workuflow/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers رفيعة
│   │   ├── Requests/           # Form Request Validation
│   │   └── Resources/          # API Resources
│   ├── Models/                 # Eloquent Models
│   ├── Modules/                # موديولات الأعمال
│   │   ├── Projects/
│   │   ├── Transactions/
│   │   ├── Debts/
│   │   ├── Budget/
│   │   ├── Recurring/
│   │   └── Reports/
│   ├── Services/               # خدمات مشتركة
│   └── Support/
│       ├── Enums/              # PHP Enums
│       ├── Traits/             # Reusable Traits
│       └── Helpers/
├── docs/                       # وثائق المشروع
│   ├── PROJECT.md
│   ├── ARCHITECTURE.md
│   └── TASKS.md
├── resources/
│   └── views/                  # Blade Templates
└── tests/
    └── Feature/                # Feature Tests
```

---

## 📦 موديولات النظام

تعكس القائمة التالية الموديولات والواجهات المسجلة الموجودة في المستودع، دون افتراض حالة نشر أو نسبة اكتمال:

| الموديول أو الواجهة | الدليل الموجود في المستودع |
|---------------------|----------------------------|
| 🔐 المصادقة والتحقق | موديول `Auth` ومسارات تسجيل الدخول والتسجيل والتحقق من البريد. |
| 📊 لوحة التحكم | موديول `Dashboard` والمسار المسمى `dashboard`. |
| 📁 المشاريع | موديول `Projects` ومسارات `projects.*`. |
| 💸 المعاملات | موديول `Transactions` ومسارات `transactions.*`. |
| 💰 المحافظ والتحويلات | نموذجا `Wallet` و`WalletTransfer` ومسارات `wallets.*`. |
| 🧾 الفواتير | نموذجا `Invoice` و`InvoiceItem` ومسارات `invoices.*`. |
| 🏷️ التصنيفات | موديول `Categories` ومسارات `categories.*`. |
| 💰 الميزانيات | موديولا `Budget` و`Budgets` ومسارات `budget.*`. |
| 🔁 المعاملات المتكررة | موديول `Recurring` ومسارات `recurring.*`. |
| 💳 الديون | موديول `Debts` ومسارات `debts.*`. |
| 📈 التقارير والتصدير | موديول `Reports` ومسارات `reports.*`. |
| 🔔 الإشعارات | موديول `Notifications` ومسارات `notifications.*`. |
| 💼 الاشتراكات والفوترة | موديول `Billing` ونموذج `Subscription` ومسارات `billing.*`. |
| 🤖 المساعد المالي الذكي | موديول `AiCopilot` ومسارا `ai-copilot.index` و`ai-copilot.analyze`. |

---

## 🧱 قواعد التطوير

> يجب الالتزام بهذه القواعد في كل سطر كود يُكتب.

### 1. Controllers رفيعة دائماً
```php
// ✅ صحيح
public function store(StoreTransactionRequest $request): RedirectResponse
{
    $transaction = app(CreateTransactionAction::class)->execute(
        TransactionData::fromRequest($request)
    );
    return redirect()->route('transactions.index')->with('success', 'تمت الإضافة');
}
```

### 2. Action Pattern لكل عملية
```php
// كل عملية = Action منفصل في app/Modules/{Module}/Actions/
class CreateTransactionAction
{
    public function execute(TransactionData $data): Transaction { ... }
}
```

### 3. BelongsToUser Trait إلزامي
```php
// كل Model يملكه المستخدم يجب أن يستخدم هذا الـ Trait
class Transaction extends Model
{
    use BelongsToUser; // عزل تلقائي لبيانات كل مستخدم
}
```

### 4. Enums بدلاً من Strings
```php
// ✅ صحيح
TransactionType::Income
// ❌ خطأ
'income'
```

### 5. لا N+1 Queries
```php
// ✅ صحيح
Project::with(['transactions', 'debts'])->get();
// ❌ خطأ — loop بدون eager loading
```

---

## 🗺️ الخطة المعمارية الأصلية

تسجل هذه القائمة مراحل التنفيذ الأصلية للمشروع، ولا تمثل أعمالاً معلقة أو حالة الموديولات الحالية.

```
Phase 1  → الأساس (DB + Enums + Models)
Phase 2  → المصادقة
Phase 3  → Layout + Components
Phase 4  → المشاريع + الشخصي/التجاري + الميزانية
Phase 5  → الفئات + Recurring
Phase 6  → المعاملات ⭐ (المحرك الأساسي)
Phase 7  → لوحة التحكم
Phase 8  → الديون
Phase 9  → التقارير
Phase 10 → الإشعارات
Phase 11 → الاشتراكات
Phase 12 → الإعدادات
Phase 13 → الأمان والجودة
Phase 14 → الإنتاج والـ API
```

---

*دراهم © 2026 — SaaS Financial Platform*
