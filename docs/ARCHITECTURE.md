# 💰 Workuflow — SaaS Financial Platform

> وثيقة معمارية شاملة للمشروع — Laravel 12 / PHP 8.3+

---

## 📋 نظرة عامة على المشروع

**Workuflow** هي منصة **SaaS مالية** (SaaS Financial Platform) حديثة مصممة للمستقلين (Freelancers)، أصحاب الأعمال الصغيرة، البائعين في التجارة الإلكترونية، وكل من لديه مصادر دخل متعددة.

### لماذا SaaS Financial Platform وليس ERP أو محاسبة تقليدية؟

| النظام | الوصف | Workuflow |
|--------|-------|-----------|
| ERP التقليدي | معقد، يحتاج خبير، مكلف | ❌ لا |
| برامج المحاسبة | مصطلحات معقدة، منحنى تعلم عالٍ | ❌ لا |
| **SaaS Financial Platform** | بسيط، سحابي، قائم على اشتراك، سهل الاستخدام | ✅ نعم |

الهدف: منح المستخدم **وضوحاً مالياً فورياً** بدون خبرة محاسبية.

### 🎯 الهدف الأساسي

**ليست** نظام محاسبة ERP تقليدياً.  
**بل** منصة وضوح مالي خفيفة تركز على البساطة، تجربة المستخدم، السرعة، وسهولة الاستخدام.

### 👥 الفئة المستهدفة

| الفئة | الاستخدام |
|-------|-----------|
| المستقلون (Freelancers) | تتبع دخل المشاريع والمصروفات |
| أصحاب الأعمال الصغيرة | إدارة متعددة للمشاريع والأرباح |
| البائعون في التجارة الإلكترونية | مراقبة الإيرادات والتكاليف |
| أصحاب مصادر الدخل المتعددة | فهم الصورة المالية الكاملة |

---

## 🛠️ التقنيات المستخدمة

### Backend
- **Laravel 12**
- **PHP 8.3+**
- **MySQL**

### Frontend
- **Blade Templates**
- **Tailwind CSS**
- **Alpine.js**

### مستقبلاً
- REST API كامل
- تطبيق Flutter للجوال
- رؤى مالية بالذكاء الاصطناعي (AI Financial Insights)
- مسح الإيصالات بـ OCR

---

## 🏗️ الهيكل المعماري

### مبدأ التصميم
```
Controller → Form Request → Action → Service → Model
```

### هيكل المجلدات الكامل

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── DashboardController.php
│   │   ├── ProjectController.php
│   │   ├── TransactionController.php
│   │   ├── CategoryController.php
│   │   ├── DebtController.php
│   │   ├── ReportController.php
│   │   └── SettingsController.php
│   ├── Middleware/
│   │   ├── EnsureSubscriptionActive.php
│   │   └── ScopeToUser.php
│   ├── Requests/
│   │   ├── Projects/
│   │   │   ├── StoreProjectRequest.php
│   │   │   └── UpdateProjectRequest.php
│   │   ├── Transactions/
│   │   │   ├── StoreTransactionRequest.php
│   │   │   └── UpdateTransactionRequest.php
│   │   └── Debts/
│   │       ├── StoreDebtRequest.php
│   │       └── UpdateDebtRequest.php
│   └── Resources/               # API Resources (جاهز للمستقبل)
│       ├── ProjectResource.php
│       ├── TransactionResource.php
│       └── DebtResource.php
│
├── Models/
│   ├── User.php
│   ├── Project.php
│   ├── Category.php
│   ├── Transaction.php
│   ├── Debt.php
│   ├── Notification.php
│   └── Subscription.php
│
├── Modules/
│   ├── Projects/
│   │   ├── Actions/
│   │   │   ├── CreateProjectAction.php
│   │   │   ├── UpdateProjectAction.php
│   │   │   └── DeleteProjectAction.php
│   │   ├── Services/
│   │   │   └── ProjectFinancialService.php
│   │   ├── DTOs/
│   │   │   └── ProjectData.php
│   │   └── Policies/
│   │       └── ProjectPolicy.php
│   │
│   ├── Transactions/
│   │   ├── Actions/
│   │   │   ├── CreateTransactionAction.php
│   │   │   ├── UpdateTransactionAction.php
│   │   │   └── DeleteTransactionAction.php
│   │   ├── Services/
│   │   │   ├── TransactionService.php
│   │   │   └── BalanceCalculatorService.php
│   │   ├── DTOs/
│   │   │   └── TransactionData.php
│   │   └── Policies/
│   │       └── TransactionPolicy.php
│   │
│   ├── Debts/
│   │   ├── Actions/
│   │   │   ├── CreateDebtAction.php
│   │   │   └── MarkDebtAsPaidAction.php
│   │   ├── Services/
│   │   │   └── DebtTrackerService.php
│   │   ├── DTOs/
│   │   │   └── DebtData.php
│   │   └── Policies/
│   │       └── DebtPolicy.php
│   │
│   └── Reports/
│       ├── Services/
│       │   ├── MonthlyReportService.php
│       │   ├── ProfitLossService.php
│       │   └── CashFlowService.php
│       └── DTOs/
│           └── ReportData.php
│
├── Services/                    # خدمات مشتركة عبر الموديولات
│   ├── CurrencyService.php
│   ├── NotificationService.php
│   └── SubscriptionService.php
│
├── Support/
│   ├── Enums/
│   │   ├── TransactionType.php
│   │   ├── DebtStatus.php
│   │   └── SubscriptionPlan.php
│   ├── Traits/
│   │   └── BelongsToUser.php
│   └── Helpers/
│       └── MoneyFormatter.php
│
└── View/
    └── Components/              # Blade Components قابلة للإعادة
        ├── StatsCard.php
        ├── TransactionRow.php
        ├── ProjectCard.php
        └── Alert.php

resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── auth.blade.php
│   ├── components/
│   │   ├── stats-card.blade.php
│   │   ├── transaction-row.blade.php
│   │   ├── project-card.blade.php
│   │   └── alert.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   ├── projects/
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── transactions/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   ├── debts/
│   │   ├── index.blade.php
│   │   └── create.blade.php
│   └── reports/
│       └── index.blade.php
```

---

## 🗄️ تصميم قاعدة البيانات

### مخطط العلاقات (ERD)

```
users
  └──< projects
          └──< transactions
          └──< debts
          └──< categories
```

### جدول المستخدمين — `users`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID / UUID | المعرّف الفريد |
| name | string | الاسم |
| email | string unique | البريد الإلكتروني |
| password | string | كلمة المرور |
| currency | string(3) | العملة الافتراضية (SAR, USD…) |
| timezone | string | المنطقة الزمنية |
| subscription_plan | enum | free / pro / business |
| email_verified_at | timestamp | توقيت التحقق |
| timestamps | — | created_at / updated_at |

### جدول المشاريع — `projects`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| name | string | اسم المشروع |
| description | text nullable | الوصف |
| color | string | لون المشروع في الواجهة |
| currency | string(3) | عملة المشروع |
| is_active | boolean | هل المشروع نشط؟ |
| timestamps | — | |

### جدول الفئات — `categories`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| name | string | اسم الفئة |
| type | enum | income / expense |
| icon | string nullable | أيقونة |
| color | string nullable | لون |
| timestamps | — | |

### جدول المعاملات — `transactions`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| project_id | FK → projects | المشروع |
| category_id | FK → categories nullable | الفئة |
| type | enum | income / expense / transfer |
| amount | decimal(15,2) | المبلغ |
| currency | string(3) | العملة |
| description | string nullable | الوصف |
| notes | text nullable | ملاحظات |
| transaction_date | date | تاريخ المعاملة |
| reference | string nullable | رقم مرجعي |
| timestamps | — | |

**Indexes:** `(user_id, transaction_date)`, `(project_id, type)`, `(user_id, type, transaction_date)`

### جدول الديون — `debts`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| project_id | FK nullable | المشروع المرتبط |
| type | enum | borrowed / lent |
| party_name | string | اسم الطرف الآخر |
| amount | decimal(15,2) | المبلغ الأصلي |
| remaining_amount | decimal(15,2) | المبلغ المتبقي |
| currency | string(3) | العملة |
| due_date | date nullable | تاريخ الاستحقاق |
| status | enum | active / partially_paid / paid |
| notes | text nullable | ملاحظات |
| timestamps | — | |

### جدول الاشتراكات — `subscriptions`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| plan | enum | free / pro / business |
| status | enum | active / cancelled / expired |
| starts_at | timestamp | بداية الاشتراك |
| ends_at | timestamp nullable | نهاية الاشتراك |
| payment_provider | string | stripe / paypal |
| provider_subscription_id | string | معرف الاشتراك عند المزود |
| timestamps | — | |

---

## ⚙️ القرارات المعمارية

### 1. نمط الـ Action Pattern
```php
// بدلاً من وضع المنطق في Controller أو Model
// كل عملية = Action مستقل

class CreateTransactionAction
{
    public function execute(TransactionData $data): Transaction
    {
        // منطق الإنشاء هنا فقط
    }
}
```

**السبب:** قابلية الاختبار، إعادة الاستخدام، وضوح المسؤوليات.

### 2. نمط DTOs للبيانات
```php
class TransactionData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $projectId,
        public readonly TransactionType $type,
        public readonly float $amount,
        public readonly string $currency,
        public readonly Carbon $transactionDate,
        public readonly ?string $description = null,
    ) {}
}
```

**السبب:** أمان الأنواع (Type Safety)، تجنب تمرير arrays غير منضبطة.

### 3. Enums للقيم الثابتة
```php
enum TransactionType: string
{
    case Income   = 'income';
    case Expense  = 'expense';
    case Transfer = 'transfer';
}

enum DebtStatus: string
{
    case Active        = 'active';
    case PartiallyPaid = 'partially_paid';
    case Paid          = 'paid';
}
```

### 4. عزل البيانات بـ Global Scope
```php
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
```

**السبب:** حماية بيانات كل مستخدم تلقائياً دون الحاجة لتكرار `where('user_id')` في كل استعلام.

### 5. Controllers رفيعة (Thin Controllers)
```php
class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $transaction = app(CreateTransactionAction::class)
            ->execute(TransactionData::fromRequest($request));

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'تم إضافة المعاملة بنجاح');
    }
}
```

---

## 📦 موديولات النظام

### 1. 🔐 المصادقة (Authentication)
- تسجيل المستخدم مع اختيار العملة والمنطقة الزمنية
- تسجيل الدخول / الخروج
- التحقق من البريد الإلكتروني
- إعادة تعيين كلمة المرور
- حماية المسارات بـ Middleware

### 2. 📊 لوحة التحكم (Dashboard)
- ملخص مالي سريع (إجمالي الدخل، المصروفات، صافي الربح)
- آخر المعاملات
- المشاريع النشطة
- الديون القريبة من الاستحقاق
- رسم بياني للتدفق النقدي (الأشهر الأخيرة)
- مؤشرات KPI بسيطة وواضحة

### 3. 📁 المشاريع (Projects)
- إنشاء وتعديل وحذف المشاريع
- عزل مالي كامل لكل مشروع
- لون مخصص لكل مشروع في الواجهة
- ملخص مالي لكل مشروع
- أرشفة المشاريع غير النشطة

### 4. 💸 المعاملات (Transactions)
- إضافة دخل / مصروف / تحويل
- تصفية حسب النوع، التاريخ، الفئة، المشروع
- بحث سريع
- تصدير CSV
- إرفاق ملاحظات ومرجع

### 5. 🏷️ الفئات (Categories)
- فئات مخصصة لكل مستخدم
- نوع الفئة (دخل / مصروف)
- أيقونة ولون لكل فئة
- فئات افتراضية عند إنشاء الحساب

### 6. 💳 الديون والالتزامات (Debts & Liabilities)
- تتبع الديون المستحقة عليك (مقترَض)
- تتبع الديون المستحقة لك (مُقرَض)
- حالة السداد (كامل / جزئي / لم يُسدَّد)
- تنبيهات عند اقتراب تاريخ الاستحقاق
- سجل مدفوعات جزئية

### 7. 📈 التقارير والتحليلات (Reports)
- تقرير الأرباح والخسائر الشهري
- تحليل التدفق النقدي
- مقارنة المشاريع
- أداء الفئات
- تصدير PDF / CSV

### 8. 🔔 الإشعارات (Notifications)
- تنبيه عند اقتراب استحقاق دين
- ملخص مالي أسبوعي
- تنبيهات تجاوز الميزانية
- إشعارات داخل التطبيق + بريد إلكتروني

### 9. 💼 الاشتراكات والفوترة (Subscriptions)
- خطة مجانية (Free) بحدود محددة
- خطة Pro وBusiness بميزات موسّعة
- تكامل مع Stripe
- فاتورة شهرية / سنوية
- إدارة طرق الدفع

### 10. ⚙️ الإعدادات (Settings)
- المعلومات الشخصية
- العملة الافتراضية والمنطقة الزمنية
- تغيير كلمة المرور
- إعدادات الإشعارات
- حذف الحساب

---

## 🎨 رؤية واجهة المستخدم

### مصادر الإلهام
- **Stripe Dashboard** — بيانات واضحة، ألوان هادئة
- **Notion** — مساحات بيضاء، قراءة مريحة
- **Linear** — تفاعل سلس، شعور احترافي

### مبادئ التصميم

| المبدأ | التطبيق |
|--------|---------|
| Minimal | لا عناصر زائدة، كل شيء له غرض |
| Mobile-First | يعمل بشكل مثالي على الجوال أولاً |
| High Readability | خطوط واضحة، تباين كافٍ |
| Dark/Light Ready | دعم الوضع الليلي من البداية |
| Smooth Interactions | Alpine.js للتفاعلات بدون reload |
| Fast Loading | Lazy loading، تحسين الاستعلامات |

---

## 📅 خطة البناء المقترحة

| المرحلة | الموديولات | الأولوية |
|---------|-----------|---------|
| **Phase 1** | Auth + User Settings + DB Schema | 🔴 عاجل |
| **Phase 2** | Projects + Categories | 🔴 عاجل |
| **Phase 3** | Transactions (المحرك الأساسي) | 🔴 عاجل |
| **Phase 4** | Dashboard + تصورات بسيطة | 🟠 مهم |
| **Phase 5** | Debts & Liabilities | 🟠 مهم |
| **Phase 6** | Reports & Analytics | 🟡 متوسط |
| **Phase 7** | Notifications | 🟡 متوسط |
| **Phase 8** | Subscriptions & Billing | 🟢 لاحقاً |

---

## ⚠️ تحذيرات قابلية التوسع المستقبلي

### 1. Multi-Currency Support
استخدم دائماً `decimal(15,2)` وخزّن العملة مع كل معاملة — لا تفترض عملة واحدة.

### 2. تجنب N+1 Queries
استخدم Eager Loading دائماً:
```php
// ❌ خطأ
$projects = Project::all();
foreach ($projects as $project) {
    echo $project->transactions->sum('amount');
}

// ✅ صحيح
$projects = Project::withSum('transactions', 'amount')->get();
```

### 3. Cache التقارير
التقارير الثقيلة يجب أن تُحسب وتُخزَّن في Cache:
```php
Cache::remember("report.{$userId}.{$month}", 3600, fn() => ...);
```

### 4. Queue للإشعارات
لا ترسل إشعارات بريد إلكتروني بشكل متزامن — استخدم Queue دائماً.

### 5. ULID بدلاً من Auto-increment
استخدم ULID/UUID للـ IDs لتسهيل الـ API والأمان.

### 6. Soft Deletes
فعّل Soft Deletes على جميع الجداول الرئيسية لتجنب فقدان البيانات.

---

## 🔧 قواعد الكود

| القاعدة | التطبيق |
|---------|---------|
| Controllers رفيعة | تفويض كل منطق للـ Actions |
| Form Requests | تحقق من صحة البيانات في طبقة منفصلة |
| Policies | صلاحيات واضحة لكل موديول |
| Typed Properties | استخدام الأنواع في كل مكان |
| Return Types | تحديد نوع الإرجاع لكل دالة |
| No Pseudo-code | كود جاهز للإنتاج فقط |
| DRY Principle | لا تكرار — استخدم Components وTraits |

---

## 🚀 الميزات المستقبلية

- [ ] REST API كامل للتطبيق المحمول
- [ ] تطبيق Flutter (iOS / Android)
- [ ] رؤى مالية بالذكاء الاصطناعي (AI Insights)
- [ ] مسح الإيصالات بـ OCR
- [ ] تكامل مع البنوك (Open Banking)
- [ ] تقارير الضرائب
- [ ] دعم متعدد المستخدمين للشركات (Teams)
- [ ] تكامل مع Zapier / n8n

---

*آخر تحديث: مايو 2026 — وثيقة حية تُحدَّث مع كل مرحلة من البناء*
