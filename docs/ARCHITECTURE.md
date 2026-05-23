# 💰 دراهم — وثيقة المعمارية التقنية

> Laravel 12 / PHP 8.2 — SaaS Financial Platform  
> آخر تحديث: مايو 2026

---

## 📋 نظرة عامة

**دراهم** منصة SaaS مالية مبنية على Laravel 12 تستهدف المستقلين وأصحاب الأعمال الصغيرة.  
تعمل بنظام **Multi-tenant** حيث بيانات كل مستخدم معزولة تلقائياً عبر `BelongsToUser` Global Scope.

---

## 🛠️ التقنيات المستخدمة

### Backend
| الحزمة | الإصدار | الاستخدام |
|--------|---------|-----------|
| Laravel | 12.x | Framework الرئيسي |
| PHP | 8.2 | لغة البرمجة |
| MySQL | 8.x | قاعدة البيانات |
| spatie/laravel-permission | ^6 | إدارة الأدوار والصلاحيات |
| filament/filament | ^3 | لوحة الإدارة |
| laravel/telescope | dev | مراقبة وتتبع الطلبات |

### Frontend
| التقنية | الاستخدام |
|---------|-----------|
| Blade Templates | قوالب الواجهة |
| Tailwind CSS v4 | التنسيق |
| Alpine.js | التفاعلات بدون reload |
| Chart.js | الرسوم البيانية |
| Tajawal Font | خط عربي احترافي |

### Testing
| الأداة | النتيجة |
|--------|---------|
| Pest PHP | 53 / 53 ✅ |

---

## 🏗️ مبدأ التصميم

```
Controller → Form Request → Action → Service → Model
```

- **Controller**: يستقبل الطلب، يُفوّض للـ Action، يُعيد الاستجابة
- **Form Request**: يتحقق من صحة البيانات (Arabic validation messages)
- **Action**: منطق عملية واحدة محددة — قابل للاختبار والإعادة
- **Service**: منطق أعمال معقد يُستخدم عبر أكثر من Action
- **Model**: البيانات والعلاقات فقط (لا منطق أعمال)

---

## 📁 هيكل المجلدات الكامل

```
app/
├── Console/
│   └── Commands/
│       ├── ProcessRecurringTransactions.php   ← Scheduler يومي 01:00
│       └── SendDebtAlerts.php                 ← Scheduler يومي 08:00
│
├── Filament/
│   ├── Resources/
│   │   ├── UserResource.php                   ← إدارة المستخدمين (CRUD)
│   │   └── TransactionResource.php            ← عرض المعاملات (قراءة فقط)
│   ├── Widgets/
│   │   ├── StatsOverviewWidget.php            ← إحصاءات كلية للمنصة
│   │   └── UsersChartWidget.php               ← رسم بياني نمو المستخدمين
│   └── Pages/                                 ← (auto-discovered)
│
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                              ← Breeze controllers
│   │   ├── BillingController.php
│   │   ├── BudgetController.php
│   │   ├── CategoryController.php
│   │   ├── DashboardController.php
│   │   ├── DebtController.php
│   │   ├── ProjectController.php
│   │   ├── RecurringController.php
│   │   ├── ReportController.php
│   │   ├── SettingsController.php
│   │   └── TransactionController.php
│   │
│   ├── Middleware/
│   │   └── EnsureSubscriptionActive.php
│   │
│   └── Requests/
│       ├── Auth/
│       ├── Billing/
│       ├── Budgets/
│       │   └── StoreBudgetRequest.php
│       ├── Categories/
│       │   ├── StoreCategoryRequest.php
│       │   └── UpdateCategoryRequest.php
│       ├── Debts/
│       │   └── StoreDebtRequest.php
│       ├── Projects/
│       │   ├── StoreProjectRequest.php
│       │   └── UpdateProjectRequest.php
│       ├── Recurring/
│       │   └── StoreRecurringRequest.php
│       └── Transactions/
│           ├── StoreTransactionRequest.php
│           └── UpdateTransactionRequest.php
│
├── Models/
│   ├── Budget.php
│   ├── Category.php
│   ├── Debt.php
│   ├── Project.php
│   ├── RecurringTransaction.php
│   ├── Subscription.php
│   ├── Transaction.php
│   └── User.php                               ← FilamentUser, HasRoles
│
├── Modules/
│   ├── Billing/
│   │   ├── Contracts/
│   │   │   └── PaymentProviderInterface.php   ← Contract للمزود المستقبلي
│   │   └── Services/
│   │       └── SubscriptionService.php
│   │
│   ├── Budgets/
│   │   ├── Actions/
│   │   │   ├── CreateBudgetAction.php
│   │   │   ├── UpdateBudgetAction.php
│   │   │   └── DeleteBudgetAction.php
│   │   ├── DTOs/
│   │   │   └── BudgetData.php
│   │   └── Services/
│   │       └── BudgetTrackerService.php
│   │
│   ├── Categories/
│   │   ├── Actions/
│   │   │   ├── CreateCategoryAction.php
│   │   │   ├── UpdateCategoryAction.php
│   │   │   └── DeleteCategoryAction.php
│   │   └── DTOs/
│   │       └── CategoryData.php
│   │
│   ├── Debts/
│   │   ├── Actions/
│   │   │   ├── CreateDebtAction.php
│   │   │   ├── RecordPartialPaymentAction.php
│   │   │   └── MarkDebtAsPaidAction.php
│   │   ├── DTOs/
│   │   │   └── DebtData.php
│   │   └── Services/
│   │       └── DebtTrackerService.php
│   │
│   ├── Projects/
│   │   ├── Actions/
│   │   │   ├── CreateProjectAction.php
│   │   │   ├── UpdateProjectAction.php
│   │   │   └── DeleteProjectAction.php
│   │   ├── DTOs/
│   │   │   └── ProjectData.php
│   │   └── Services/
│   │       └── ProjectFinancialService.php
│   │
│   ├── Recurring/
│   │   ├── Actions/
│   │   │   ├── CreateRecurringAction.php
│   │   │   ├── UpdateRecurringAction.php
│   │   │   ├── ToggleRecurringAction.php
│   │   │   └── ProcessRecurringAction.php     ← ينشئ Transaction + يُحدّث next_due_date
│   │   ├── DTOs/
│   │   │   └── RecurringData.php
│   │   └── Services/
│   │       └── RecurringService.php
│   │
│   ├── Reports/
│   │   └── Services/
│   │       ├── CashFlowService.php
│   │       ├── MonthlyReportService.php
│   │       ├── ProfitLossService.php
│   │       └── TopCategoriesService.php
│   │
│   └── Transactions/
│       ├── Actions/
│       │   ├── CreateTransactionAction.php
│       │   ├── UpdateTransactionAction.php
│       │   └── DeleteTransactionAction.php
│       ├── DTOs/
│       │   └── TransactionData.php
│       └── Services/
│           ├── TransactionService.php
│           └── BalanceCalculatorService.php
│
├── Policies/
│   ├── BudgetPolicy.php
│   ├── CategoryPolicy.php
│   ├── DebtPolicy.php
│   ├── ProjectPolicy.php
│   ├── RecurringPolicy.php
│   └── TransactionPolicy.php
│
├── Providers/
│   ├── AppServiceProvider.php                 ← تسجيل جميع Policies
│   └── Filament/
│       └── AdminPanelProvider.php             ← Panel /admin
│
└── Support/
    ├── Enums/
    │   ├── DebtStatus.php
    │   ├── DebtType.php
    │   ├── ProjectType.php
    │   ├── RecurringFrequency.php             ← مع nextDate(Carbon): Carbon
    │   ├── SubscriptionPlan.php
    │   └── TransactionType.php
    ├── Traits/
    │   └── BelongsToUser.php                  ← Global Scope عزل تلقائي
    └── Helpers/
        └── MoneyFormatter.php

resources/views/
├── layouts/
│   ├── app.blade.php                          ← Sidebar + Topbar (RTL)
│   └── auth.blade.php
├── components/
│   ├── badge.blade.php
│   ├── empty-state.blade.php
│   ├── modal.blade.php
│   ├── nav-item.blade.php
│   ├── progress-bar.blade.php
│   └── stats-card.blade.php
├── billing/
│   ├── index.blade.php                        ← صفحة أسعار 3 خطط
│   └── success.blade.php
├── budget/
│   └── index.blade.php
├── categories/
│   └── index.blade.php
├── dashboard/
│   └── index.blade.php
├── debts/
│   ├── index.blade.php
│   └── create.blade.php
├── projects/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── _form.blade.php
├── recurring/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── reports/
│   └── index.blade.php
├── settings/
│   └── index.blade.php
└── transactions/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php

config/
├── billing.php                                ← provider, plans, credentials
└── stripe.php                                 ← فارغ (محجوز لتجنب cache errors)

database/
├── migrations/
│   ├── ..._create_users_table.php
│   ├── ..._create_projects_table.php
│   ├── ..._create_categories_table.php
│   ├── ..._create_transactions_table.php
│   ├── ..._create_debts_table.php
│   ├── ..._create_budgets_table.php
│   ├── ..._create_recurring_transactions_table.php
│   ├── ..._create_subscriptions_table.php
│   ├── ..._add_payment_customer_id_to_users_table.php
│   └── (spatie permission migrations)
└── seeders/
    ├── AdminSeeder.php                        ← super_admin role + admin user
    └── DatabaseSeeder.php

routes/
└── web.php                                    ← جميع الـ routes

docs/
├── PROJECT.md                                 ← وصف المشروع والأهداف
├── ARCHITECTURE.md                            ← هذا الملف
└── TASKS.md                                   ← تتبع المهام
```

---

## 🗄️ تصميم قاعدة البيانات

### جدول المستخدمين — `users`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف الفريد |
| name | string | الاسم |
| email | string unique | البريد الإلكتروني |
| password | string | كلمة المرور (bcrypt) |
| currency | string(3) | العملة الافتراضية (SAR) |
| timezone | string | المنطقة الزمنية |
| subscription_plan | enum | free / pro / business |
| payment_customer_id | string nullable | معرف العميل عند مزود الدفع (generic) |
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
| type | enum | personal / business |
| is_active | boolean | هل المشروع نشط؟ |
| deleted_at | timestamp | Soft Delete |
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
| project_id | FK → projects nullable | المشروع |
| category_id | FK → categories nullable | الفئة |
| type | enum | income / expense / transfer |
| amount | decimal(15,2) | المبلغ |
| currency | string(3) | العملة |
| description | string nullable | الوصف |
| notes | text nullable | ملاحظات |
| transaction_date | date | تاريخ المعاملة |
| reference | string nullable | رقم مرجعي |
| deleted_at | timestamp | Soft Delete |
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
| deleted_at | timestamp | Soft Delete |
| timestamps | — | |

### جدول الميزانية — `budgets`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| category_id | FK → categories nullable | الفئة |
| amount | decimal(15,2) | المبلغ المخصص |
| period | enum | monthly / yearly |
| month | tinyInteger nullable | الشهر (1-12) |
| year | smallInteger | السنة |
| timestamps | — | |

### جدول المتكررة — `recurring_transactions`

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | ULID | المعرّف |
| user_id | FK → users | المالك |
| category_id | FK nullable | الفئة |
| project_id | FK nullable | المشروع |
| type | enum | income / expense |
| amount | decimal(15,2) | المبلغ |
| description | string | الوصف |
| frequency | enum | daily / weekly / monthly / yearly |
| start_date | date | تاريخ البدء |
| end_date | date nullable | تاريخ الانتهاء (null = مفتوح) |
| next_due_date | date | التاريخ التالي للمعالجة |
| is_active | boolean | هل مفعّل؟ |
| currency | string(3) | العملة |
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
| payment_provider | string | اسم المزود (manual / أي مزود) |
| provider_subscription_id | string nullable | معرف الاشتراك عند المزود |
| timestamps | — | |

---

## ⚙️ القرارات المعمارية

### 1. BelongsToUser — عزل البيانات التلقائي

```php
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
```

- يُطبَّق تلقائياً على: Project, Category, Transaction, Debt, Budget, RecurringTransaction
- في سياق CLI (الـ Scheduler): `auth()->check()` يُرجع `false` — الـ Scope لا يُطبَّق — كل البيانات متاحة
- في Filament Admin: `withoutGlobalScopes()` مطلوب صراحةً في `modifyQueryUsing()`

### 2. نمط الـ Action Pattern

```php
// كل عملية = Action مستقل قابل للاختبار
class CreateProjectAction
{
    public function execute(ProjectData $data): Project
    {
        return Project::create($data->toArray());
    }
}
```

### 3. DTOs بـ fromRequest()

```php
class ProjectData
{
    public function __construct(
        public readonly string $name,
        public readonly string $color,
        public readonly ProjectType $type,
        // ...
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            color: $data['color'],
            type: ProjectType::from($data['type']),
            // ...
        );
    }
}
```

> **ملاحظة:** الـ DTOs تستخدم `fromRequest(array $data)` وليس `fromArray()`.

### 4. RecurringFrequency::nextDate()

```php
enum RecurringFrequency: string
{
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function nextDate(Carbon $from): Carbon
    {
        return match($this) {
            self::Daily   => $from->addDay(),
            self::Weekly  => $from->addWeek(),
            self::Monthly => $from->addMonth(),
            self::Yearly  => $from->addYear(),
        };
    }
}
```

### 5. ProcessRecurringAction — المنطق في CLI

```php
class ProcessRecurringAction
{
    public function execute(RecurringTransaction $recurring): Transaction
    {
        // صريح في user_id لأن auth() فارغ في CLI
        $transaction = Transaction::create([
            'user_id' => $recurring->user_id,
            'amount'  => $recurring->amount,
            // ...
        ]);

        $nextDate = $recurring->frequency->nextDate($recurring->next_due_date);
        $recurring->update(['next_due_date' => $nextDate]);

        if ($recurring->end_date && $nextDate->gt($recurring->end_date)) {
            $recurring->update(['is_active' => false]);
        }

        return $transaction;
    }
}
```

### 6. PaymentProviderInterface — تجهيز لمزود الدفع

```php
interface PaymentProviderInterface
{
    public function createCheckoutUrl(User $user, string $plan): string;
    public function createPortalUrl(User $user): string;
    public function parseWebhook(string $payload, string $signature): array;
    // returns: ['event' => string, 'data' => array]
}
```

عند إضافة مزود دفع:
1. إنشاء class ينفذ `PaymentProviderInterface`
2. تسجيله في `AppServiceProvider` عبر `app()->bind()`
3. تحديث `config/billing.php` — ضبط `BILLING_PROVIDER` في `.env`
4. إكمال الـ TODOs في `BillingController`

### 7. Filament Admin — حماية الوصول

```php
// User.php
public function canAccessPanel(Panel $panel): bool
{
    return $this->hasRole('super_admin');
}

// TransactionResource.php — قراءة بيانات جميع المستخدمين
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->withoutGlobalScopes()->with(['user', 'category', 'project']))
        // ...
}
```

---

## 📅 Scheduled Commands

| Command | التوقيت | الوظيفة |
|---------|---------|---------|
| `recurring:process` | يومياً 01:00 | معالجة المعاملات المتكررة المستحقة |
| `debts:send-alerts` | يومياً 08:00 | إرسال تنبيهات الديون القريبة من الاستحقاق |

```php
// bootstrap/app.php أو Console/Kernel.php
Schedule::command('recurring:process')->dailyAt('01:00');
Schedule::command('debts:send-alerts')->dailyAt('08:00');
```

---

## 🔒 Policies المسجّلة

```php
// AppServiceProvider.php
Gate::policy(Budget::class, BudgetPolicy::class);
Gate::policy(Category::class, CategoryPolicy::class);
Gate::policy(Debt::class, DebtPolicy::class);
Gate::policy(Project::class, ProjectPolicy::class);
Gate::policy(RecurringTransaction::class, RecurringPolicy::class);
Gate::policy(Transaction::class, TransactionPolicy::class);
```

---

## 🗺️ مسارات التطبيق (Routes)

```php
// routes/web.php — داخل auth middleware
Route::resource('projects', ProjectController::class);
Route::resource('categories', CategoryController::class);
Route::resource('transactions', TransactionController::class);
Route::resource('debts', DebtController::class);
Route::resource('budget', BudgetController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('recurring', RecurringController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::post('/recurring/{recurring}/toggle', ...)->name('recurring.toggle');
Route::post('/recurring/{recurring}/process-now', ...)->name('recurring.process-now');

// Billing
Route::prefix('billing')->name('billing.')->group(function () {
    Route::get('/', [BillingController::class, 'index'])->name('index');
    Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
    Route::get('/success', [BillingController::class, 'success'])->name('success');
    Route::post('/portal', [BillingController::class, 'portal'])->name('portal');
});

// Webhook — خارج CSRF middleware
Route::post('/billing/webhook', [BillingController::class, 'webhook'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('billing.webhook');
```

---

## 📦 Filament Admin Panel

| المكوّن | الوصف |
|--------|-------|
| URL | `/admin` |
| الحماية | `super_admin` role (spatie) |
| اللون | Indigo |
| UserResource | CRUD كامل + roles management |
| TransactionResource | قراءة فقط (withoutGlobalScopes) |
| StatsOverviewWidget | إحصاءات المنصة الكلية |
| UsersChartWidget | نمو المستخدمين (12 شهراً) |
| AdminSeeder | `admin@workuflow.com` / `Admin@123` |

---

## ⚠️ قواعد مهمة للتطوير

| القاعدة | التطبيق |
|---------|---------|
| لا منطق في Controllers | فوّض للـ Actions |
| DTOs تستخدم `fromRequest()` | ليس `fromArray()` |
| BelongsToUser في CLI | لا يُطبَّق تلقائياً — كل البيانات متاحة |
| withoutGlobalScopes() في Filament | مطلوب صراحةً في كل Resource يحتاج cross-user data |
| payment_customer_id | Generic — ليس stripe-specific |
| اختبارات Pest | 53/53 — يجب أن تبقى خضراء |

---

## 🌐 Landing Page التسويقية

**الملف:** `resources/views/welcome.blade.php`  
**المسار:** `/` (الصفحة الرئيسية للمنصة)

### المحتوى
| القسم | الوصف |
|-------|-------|
| Navbar | ذكي — يتغير حسب `@auth`: لوحة التحكم أو ابدأ مجاناً |
| Hero | عنوان رئيسي + معاينة Dashboard (CSS خالص — KPIs + Chart) |
| Pain Points | ٦ مشاكل يعانيها المستخدم المستهدف |
| Features | ٨ مميزات مع أيقونات |
| How It Works | ٤ خطوات بصرية |
| Stats | أرقام المنصة |
| Testimonials | ٣ آراء مستخدمين |
| Pricing | ٣ خطط مرتبطة بـ `billing.index` |
| CTA | دعوة للتسجيل |
| Footer | روابط حقيقية للمسارات |

### Route
```php
// routes/web.php
Route::get('/', function () {
    return view('welcome');
})->name('home');
```

> **ملاحظة:** الـ Navbar يعرض "لوحة التحكم" للمستخدم المسجّل تلقائياً عبر `@auth` — لا redirect.

---

## 🛡️ خطة تطوير Admin المتقدم (مقترح)

مبني على Phase 15 الحالية. الأولويات:

| المهمة | الأولوية | الوصف التقني |
|--------|----------|-------------|
| SubscriptionResource | 🔴 | CRUD + Actions: activatePlan, cancelPlan, extendMonth |
| UserResource Actions | 🔴 | suspend, resetPlan, sendMail, deleteData |
| RevenueWidget | 🟠 | MRR + ARR + Churn + Donut Chart للخطط |
| SystemHealthWidget | 🟡 | Queue status + Failed Jobs + آخر Scheduler run |

---

## 🚀 الميزات المستقبلية

- [ ] ربط مزود الدفع (Tap / Paddle / غيره) — تنفيذ `PaymentProviderInterface`
- [ ] REST API كامل — Laravel Sanctum
- [ ] تطبيق Flutter (iOS / Android)
- [ ] رؤى مالية بالذكاء الاصطناعي (AI Insights)
- [ ] مسح الإيصالات بـ OCR
- [ ] تكامل مع البنوك (Open Banking)
- [ ] دعم الفرق (Teams / Multi-user)
- [ ] تكامل مع Zapier / n8n
- [ ] SubscriptionResource في Filament

---

*آخر تحديث: مايو 2026 — وثيقة حية تُحدَّث مع كل تغيير معماري*
