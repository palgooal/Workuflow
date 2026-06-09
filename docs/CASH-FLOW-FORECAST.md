# محرك توقع التدفق النقدي — Cash Flow Forecast Engine

> وثيقة المواصفات الكاملة | دراهم SaaS Financial Platform  
> الإصدار: 1.0.0 | تاريخ الإنشاء: 8 يونيو 2026 | المرحلة: Phase 26 — Sprint 2

---

## 1. نظرة عامة (Executive Overview)

**محرك توقع التدفق النقدي** يُحوّل دراهم من نظام تسجيل مالي إلى **مستشار مالي استباقي** — يُخبر المستخدم بما سيحدث لسيولته خلال الـ 30/60/90 يوماً القادمة قبل أن يقع في مشكلة مالية.

### السؤال الذي يجيب عليه المحرك

> **"هل ستكون سيولتي كافية لتغطية التزاماتي القادمة؟"**

### مصادر البيانات

```
التدفق النقدي المتوقع =
  (+) الفواتير المستحقة الدفع (Sent/Overdue)
  (+) المعاملات المتكررة (الدخل)
  (+) توقع إيرادات المشاريع النشطة
  (+) الديون التي لك (مستحقة القبض)
  (−) المعاملات المتكررة (المصروفات)
  (−) الديون التي عليك (مستحقة الدفع)
  (−) المدفوعات المجدولة المعروفة
```

---

## 2. خوارزمية التوقع (Forecast Algorithm)

### 2.1 المبدأ الأساسي

```
ForecastDay(date) = OpeningBalance(date) + ExpectedInflows(date) - ExpectedOutflows(date)
```

### 2.2 حساب الرصيد الافتتاحي

```php
/**
 * الرصيد الافتتاحي = مجموع كل المعاملات الفعلية حتى اليوم
 * مُجمَّع per-currency إذا كان المستخدم يستخدم عملات متعددة
 */
$openingBalance = Transaction::where('user_id', $userId)
    ->where('transaction_date', '<=', today())
    ->groupBy('currency')
    ->selectRaw('currency, SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
    ->get()
    ->pluck('balance', 'currency');
```

### 2.3 التدفقات الداخلة المتوقعة (Expected Inflows)

#### المصدر 1: الفواتير المستحقة
```php
$invoiceInflows = Invoice::where('user_id', $userId)
    ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
    ->whereNull('paid_at')
    ->where('due_date', '<=', $forecastEndDate)
    ->get()
    ->map(function (Invoice $invoice) {
        return new ForecastEntry(
            date: $invoice->due_date,
            amount: $invoice->total,
            currency: $invoice->currency,
            type: ForecastEntryType::InvoiceExpected,
            confidence: $this->calculateInvoiceConfidence($invoice),
            source_id: $invoice->id,
            label: "فاتورة {$invoice->number}",
        );
    });
```

**حساب Confidence للفاتورة:**
```php
private function calculateInvoiceConfidence(Invoice $invoice): float {
    $confidence = 1.0;

    // تأثير التأخر: −10% لكل 7 أيام تأخير
    if ($invoice->due_date->isPast()) {
        $daysOverdue = $invoice->due_date->diffInDays(today());
        $confidence -= ($daysOverdue / 7) * 0.10;
    }

    // تأثير سجل العميل
    $clientPayRate = $this->getClientPaymentRate($invoice->client_id);
    $confidence *= $clientPayRate;

    // تأثير حالة الفاتورة
    if ($invoice->status === InvoiceStatus::Overdue) $confidence *= 0.7;

    return max(0.1, min(1.0, $confidence));
}
```

#### المصدر 2: المعاملات المتكررة (الدخل)
```php
$recurringInflows = RecurringTransaction::where('user_id', $userId)
    ->where('type', TransactionType::Income)
    ->where('is_active', true)
    ->get()
    ->flatMap(fn ($r) => $this->generateOccurrences($r, $forecastEndDate))
    ->map(fn ($occurrence) => new ForecastEntry(
        date: $occurrence['date'],
        amount: $occurrence['amount'],
        currency: $occurrence['currency'],
        type: ForecastEntryType::RecurringIncome,
        confidence: 0.95, // عالي جداً — مجدوَل
        label: $occurrence['label'],
    ));
```

#### المصدر 3: توقع إيرادات المشاريع
```php
private function forecastProjectRevenue(int $userId, Carbon $endDate): Collection {
    return Project::where('user_id', $userId)
        ->where('is_active', true)
        ->get()
        ->map(function (Project $project) use ($endDate) {
            $avgMonthlyRevenue = $this->calculateAvgMonthlyRevenue($project);
            $daysLeft = today()->diffInDays($endDate);
            $expectedRevenue = ($avgMonthlyRevenue / 30) * $daysLeft;

            return new ForecastEntry(
                date: $endDate,
                amount: $expectedRevenue,
                currency: $project->currency,
                type: ForecastEntryType::ProjectRevenue,
                confidence: 0.60, // متوسط — غير مؤكد
                label: "توقع إيرادات: {$project->name}",
            );
        })->filter(fn ($e) => $e->amount > 0);
}
```

### 2.4 التدفقات الخارجة المتوقعة (Expected Outflows)

#### المصدر 1: المعاملات المتكررة (المصروفات)
```php
$recurringOutflows = RecurringTransaction::where('user_id', $userId)
    ->where('type', TransactionType::Expense)
    ->where('is_active', true)
    ->get()
    ->flatMap(fn ($r) => $this->generateOccurrences($r, $forecastEndDate));
```

#### المصدر 2: الديون المستحقة
```php
$debtOutflows = Debt::where('user_id', $userId)
    ->where('type', DebtType::Borrowed)
    ->whereIn('status', [DebtStatus::Active, DebtStatus::PartiallyPaid])
    ->whereNotNull('due_date')
    ->where('due_date', '<=', $forecastEndDate)
    ->get()
    ->map(fn ($debt) => new ForecastEntry(
        date: $debt->due_date,
        amount: $debt->remaining_amount,
        currency: 'ILS', // من currency المستخدم
        type: ForecastEntryType::DebtPayment,
        confidence: 0.90,
        label: "سداد دين: {$debt->description}",
    ));
```

### 2.5 حساب درجة الثقة الإجمالية (Confidence Score)

```php
public function calculateOverallConfidence(ForecastResult $result): float {
    $totalWeightedConfidence = 0;
    $totalAmount = 0;

    foreach ($result->entries as $entry) {
        $totalWeightedConfidence += $entry->confidence * abs($entry->amount);
        $totalAmount += abs($entry->amount);
    }

    if ($totalAmount === 0) return 1.0;
    return round($totalWeightedConfidence / $totalAmount, 2);
}
```

**تفسير درجة الثقة:**

| النطاق | التفسير |
|--------|---------|
| 0.85 − 1.00 | ثقة عالية — معظم الأرقام محددة ومؤكدة |
| 0.65 − 0.84 | ثقة متوسطة — بعض التوقعات |
| 0.00 − 0.64 | ثقة منخفضة — معظمها تقديرات |

### 2.6 حساب مستوى المخاطر (Risk Level)

```php
public function assessRiskLevel(ForecastResult $result, float $openingBalance): ForecastRiskLevel {
    $minProjectedBalance = $openingBalance;

    foreach ($result->dailySnapshots as $snapshot) {
        $minProjectedBalance = min($minProjectedBalance, $snapshot->cumulativeBalance);
    }

    // العجز الحرج: الرصيد يصل لأقل من 10% من متوسط المصروفات الشهرية
    $avgMonthlyExpenses = $this->calculateAvgMonthlyExpenses($result->userId);
    $criticalThreshold = $avgMonthlyExpenses * 0.10;

    if ($minProjectedBalance < 0) {
        return ForecastRiskLevel::Critical; // عجز نقدي حقيقي
    }

    if ($minProjectedBalance < $criticalThreshold) {
        return ForecastRiskLevel::High; // سيولة حرجة جداً
    }

    if ($minProjectedBalance < $avgMonthlyExpenses * 0.30) {
        return ForecastRiskLevel::Medium; // سيولة منخفضة
    }

    return ForecastRiskLevel::Low; // وضع مريح
}
```

---

## 3. تصميم قاعدة البيانات (Database Design)

### 3.1 جدول نتائج التوقع — `cash_flow_forecasts`

```sql
CREATE TABLE cash_flow_forecasts (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid                CHAR(26) NOT NULL UNIQUE,
    user_id             BIGINT UNSIGNED NOT NULL,
    
    -- نطاق التوقع
    forecast_date       DATE NOT NULL,              -- يوم إنشاء التوقع
    period_days         TINYINT UNSIGNED NOT NULL,  -- 30 | 60 | 90
    start_date          DATE NOT NULL,
    end_date            DATE NOT NULL,
    currency            VARCHAR(3) NOT NULL DEFAULT 'ILS',
    
    -- الأرصدة
    opening_balance     DECIMAL(15,2) NOT NULL,     -- الرصيد الافتتاحي
    expected_inflows    DECIMAL(15,2) NOT NULL,     -- إجمالي الداخل المتوقع
    expected_outflows   DECIMAL(15,2) NOT NULL,     -- إجمالي الخارج المتوقع
    projected_balance   DECIMAL(15,2) NOT NULL,     -- الرصيد المتوقع النهائي
    min_projected_balance DECIMAL(15,2) NOT NULL,   -- أدنى رصيد متوقع خلال الفترة
    
    -- التقييم
    confidence_score    DECIMAL(4,2) NOT NULL,      -- 0.00 − 1.00
    risk_level          VARCHAR(20) NOT NULL,        -- ForecastRiskLevel enum
    
    -- التفاصيل (JSON)
    daily_snapshots     JSON NULL,                   -- رصيد يومي مفصّل
    inflow_breakdown    JSON NULL,                   -- تفاصيل الداخل بالنوع
    outflow_breakdown   JSON NULL,                   -- تفاصيل الخارج بالنوع
    alerts              JSON NULL,                   -- التنبيهات المُكتشَفة
    
    -- metadata
    generated_at        TIMESTAMP NOT NULL,
    expires_at          TIMESTAMP NULL,              -- صلاحية النتيجة (24 ساعة)
    
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, forecast_date),
    INDEX idx_user_period (user_id, period_days),
    INDEX idx_risk_level (risk_level)
);
```

### 3.2 جدول التنبيهات المالية — `financial_alerts`

```sql
CREATE TABLE financial_alerts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid            CHAR(26) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    forecast_id     BIGINT UNSIGNED NULL,
    
    type            VARCHAR(50) NOT NULL,        -- FinancialAlertType enum
    severity        VARCHAR(20) NOT NULL,        -- critical | high | medium | low
    title           VARCHAR(255) NOT NULL,
    message         TEXT NOT NULL,
    action_link     VARCHAR(500) NULL,           -- رابط الإجراء المقترح
    
    data            JSON NULL,                   -- بيانات إضافية
    
    is_read         TINYINT(1) DEFAULT 0,
    is_dismissed    TINYINT(1) DEFAULT 0,
    read_at         TIMESTAMP NULL,
    
    -- متى يظهر التنبيه
    trigger_date    DATE NULL,                   -- التاريخ المتوقع للحدث
    days_until      SMALLINT NULL,               -- كم يوم حتى الحدث
    
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (forecast_id) REFERENCES cash_flow_forecasts(id) ON DELETE SET NULL,
    INDEX idx_user_unread (user_id, is_read, is_dismissed),
    INDEX idx_user_severity (user_id, severity)
);
```

---

## 4. معمارية النظام (System Architecture)

### 4.1 Enums

```php
// app/Modules/Forecast/Enums/ForecastRiskLevel.php
enum ForecastRiskLevel: string {
    case Low      = 'low';      // وضع مريح 🟢
    case Medium   = 'medium';   // يستحق المتابعة 🟡
    case High     = 'high';     // خطر سيولة 🟠
    case Critical = 'critical'; // عجز نقدي متوقع 🔴

    public function label(): string { /* Arabic */ }
    public function color(): string { /* Tailwind color class */ }
    public function icon(): string { /* Emoji */ }
    public function description(): string { /* وصف مفصّل */ }
}

// app/Modules/Forecast/Enums/ForecastEntryType.php
enum ForecastEntryType: string {
    case InvoiceExpected  = 'invoice_expected';   // فاتورة مستحقة
    case RecurringIncome  = 'recurring_income';   // دخل متكرر
    case ProjectRevenue   = 'project_revenue';    // توقع مشروع
    case DebtReceivable   = 'debt_receivable';    // دين لك
    case RecurringExpense = 'recurring_expense';  // مصروف متكرر
    case DebtPayment      = 'debt_payment';       // سداد دين
    case ScheduledPayment = 'scheduled_payment';  // دفعة مجدولة
}

// app/Modules/Forecast/Enums/FinancialAlertType.php
enum FinancialAlertType: string {
    case CashShortageExpected   = 'cash_shortage_expected';   // عجز متوقع
    case LowLiquidityWarning    = 'low_liquidity_warning';    // سيولة منخفضة
    case OverdueInvoiceRisk     = 'overdue_invoice_risk';     // خطر عدم التحصيل
    case LargePaymentDue        = 'large_payment_due';        // دفعة كبيرة قادمة
    case RecurringExpenseAlert  = 'recurring_expense_alert';  // مصروف متكرر قادم
    case PositiveTrendDetected  = 'positive_trend_detected';  // اتجاه إيجابي
}
```

### 4.2 DTOs

```php
// app/Modules/Forecast/DTOs/ForecastRequestDTO.php
readonly class ForecastRequestDTO {
    public function __construct(
        public int $userId,
        public int $periodDays,      // 30 | 60 | 90
        public string $currency,
        public bool $useCache = true,
    ) {
        assert(in_array($this->periodDays, [30, 60, 90]));
    }
}

// app/Modules/Forecast/DTOs/ForecastEntryDTO.php
readonly class ForecastEntryDTO {
    public function __construct(
        public Carbon $date,
        public float $amount,        // موجب = دخل، سالب = مصروف
        public string $currency,
        public ForecastEntryType $type,
        public float $confidence,    // 0.00 − 1.00
        public string $label,
        public ?int $sourceId = null,
        public ?string $sourceType = null,
    ) {}

    public function weightedAmount(): float {
        return $this->amount * $this->confidence;
    }
}

// app/Modules/Forecast/DTOs/ForecastResultDTO.php
readonly class ForecastResultDTO {
    public function __construct(
        public int $userId,
        public int $periodDays,
        public Carbon $startDate,
        public Carbon $endDate,
        public string $currency,
        public float $openingBalance,
        public float $expectedInflows,
        public float $expectedOutflows,
        public float $projectedBalance,
        public float $minProjectedBalance,
        public float $confidenceScore,    // 0.00 − 1.00
        public ForecastRiskLevel $riskLevel,
        public array $dailySnapshots,     // [date => [inflow, outflow, balance]]
        public array $inflowBreakdown,    // per ForecastEntryType
        public array $outflowBreakdown,   // per ForecastEntryType
        public array $alerts,             // ForecastAlertDTO[]
        public Carbon $generatedAt,
    ) {
        assert($this->confidenceScore >= 0 && $this->confidenceScore <= 1);
    }

    public function netCashFlow(): float {
        return $this->expectedInflows - $this->expectedOutflows;
    }

    public function isPositive(): bool {
        return $this->projectedBalance >= $this->openingBalance;
    }
}
```

### 4.3 CashFlowForecastService

```php
// app/Modules/Forecast/Services/CashFlowForecastService.php
class CashFlowForecastService {

    private const CACHE_TTL_HOURS = 6;

    public function __construct(
        private InvoiceInflowCalculator $invoiceCalculator,
        private RecurringInflowCalculator $recurringInflowCalc,
        private RecurringOutflowCalculator $recurringOutflowCalc,
        private ProjectRevenueEstimator $projectEstimator,
        private DebtCalculator $debtCalculator,
        private AlertDetector $alertDetector,
        private ForecastRepository $repository,
    ) {}

    /**
     * الحصول على توقع (من الكاش أو إنشاء جديد)
     */
    public function getForecast(ForecastRequestDTO $request): ForecastResultDTO {
        if ($request->useCache) {
            $cached = $this->repository->findRecent($request->userId, $request->periodDays, $request->currency);
            if ($cached && $cached->generatedAt->diffInHours(now()) < self::CACHE_TTL_HOURS) {
                return $cached;
            }
        }

        return $this->generateForecast($request);
    }

    /**
     * إنشاء توقع جديد
     */
    public function generateForecast(ForecastRequestDTO $request): ForecastResultDTO {
        $startDate = today();
        $endDate   = today()->addDays($request->periodDays);

        // 1. الرصيد الافتتاحي
        $openingBalance = $this->calculateOpeningBalance($request->userId, $request->currency);

        // 2. جمع جميع إدخالات التوقع
        $entries = collect()
            ->merge($this->invoiceCalculator->calculate($request->userId, $endDate))
            ->merge($this->recurringInflowCalc->calculate($request->userId, $endDate))
            ->merge($this->projectEstimator->estimate($request->userId, $endDate))
            ->merge($this->recurringOutflowCalc->calculate($request->userId, $endDate))
            ->merge($this->debtCalculator->calculateOutflows($request->userId, $endDate))
            ->merge($this->debtCalculator->calculateInflows($request->userId, $endDate))
            ->sortBy('date');

        // 3. بناء اللقطات اليومية
        $dailySnapshots = $this->buildDailySnapshots($entries, $openingBalance, $startDate, $endDate);

        // 4. حساب الإجماليات
        $inflows  = $entries->where('amount', '>', 0)->sum(fn ($e) => $e->weightedAmount());
        $outflows = $entries->where('amount', '<', 0)->sum(fn ($e) => abs($e->weightedAmount()));

        $projectedBalance    = $openingBalance + $inflows - $outflows;
        $minProjectedBalance = collect($dailySnapshots)->min('cumulative_balance');

        // 5. التقييم
        $confidenceScore = $this->calculateConfidence($entries);
        $riskLevel       = $this->assessRisk($minProjectedBalance, $request->userId);

        // 6. التنبيهات
        $alerts = $this->alertDetector->detect($dailySnapshots, $entries, $request->userId);

        // 7. بناء النتيجة
        $result = new ForecastResultDTO(
            userId: $request->userId,
            periodDays: $request->periodDays,
            startDate: $startDate,
            endDate: $endDate,
            currency: $request->currency,
            openingBalance: $openingBalance,
            expectedInflows: $inflows,
            expectedOutflows: $outflows,
            projectedBalance: $projectedBalance,
            minProjectedBalance: $minProjectedBalance,
            confidenceScore: $confidenceScore,
            riskLevel: $riskLevel,
            dailySnapshots: $dailySnapshots,
            inflowBreakdown: $this->buildBreakdown($entries->where('amount', '>', 0)),
            outflowBreakdown: $this->buildBreakdown($entries->where('amount', '<', 0)),
            alerts: $alerts,
            generatedAt: now(),
        );

        // 8. الحفظ
        $this->repository->save($result);
        event(new ForecastGenerated($result));

        return $result;
    }

    private function buildDailySnapshots(Collection $entries, float $openingBalance, Carbon $start, Carbon $end): array {
        $snapshots = [];
        $balance = $openingBalance;
        $current = $start->copy();

        while ($current <= $end) {
            $dayEntries = $entries->filter(fn ($e) => $e->date->isSameDay($current));
            $dayInflow  = $dayEntries->where('amount', '>', 0)->sum('amount');
            $dayOutflow = $dayEntries->where('amount', '<', 0)->sum(fn ($e) => abs($e->amount));
            $balance   += $dayInflow - $dayOutflow;

            $snapshots[$current->format('Y-m-d')] = [
                'date'               => $current->format('Y-m-d'),
                'inflow'             => $dayInflow,
                'outflow'            => $dayOutflow,
                'net'                => $dayInflow - $dayOutflow,
                'cumulative_balance' => round($balance, 2),
            ];

            $current->addDay();
        }

        return $snapshots;
    }
}
```

### 4.4 AlertDetector Service

```php
// app/Modules/Forecast/Services/AlertDetector.php
class AlertDetector {

    public function detect(array $dailySnapshots, Collection $entries, int $userId): array {
        $alerts = [];
        $avgMonthlyExpenses = $this->getAvgMonthlyExpenses($userId);

        foreach ($dailySnapshots as $date => $snapshot) {
            $balance = $snapshot['cumulative_balance'];
            $daysUntil = today()->diffInDays(Carbon::parse($date));

            // 🔴 عجز نقدي
            if ($balance < 0) {
                $alerts[] = [
                    'type'     => FinancialAlertType::CashShortageExpected->value,
                    'severity' => 'critical',
                    'title'    => 'عجز نقدي متوقع',
                    'message'  => "يُتوقع وصول رصيدك إلى {$balance} ₪ خلال {$daysUntil} يوماً",
                    'trigger_date' => $date,
                    'days_until'   => $daysUntil,
                    'action_link'  => route('invoices.index', ['status' => 'sent']),
                ];
                break; // أهم تنبيه واحد بالعجز يكفي
            }

            // 🟠 سيولة منخفضة
            if ($balance < $avgMonthlyExpenses * 0.25 && $balance > 0) {
                $alerts[] = [
                    'type'     => FinancialAlertType::LowLiquidityWarning->value,
                    'severity' => 'high',
                    'title'    => "سيولة منخفضة خلال {$daysUntil} يوماً",
                    'message'  => "رصيدك سيكون {$balance} ₪ — أقل من ربع مصروفاتك الشهرية",
                    'trigger_date' => $date,
                    'days_until'   => $daysUntil,
                    'action_link'  => route('forecast.index'),
                ];
            }
        }

        // فواتير كبيرة متأخرة
        $overdueRisk = $entries->filter(fn ($e) =>
            $e->type === ForecastEntryType::InvoiceExpected &&
            $e->confidence < 0.5 &&
            $e->amount > ($avgMonthlyExpenses * 0.5)
        );

        if ($overdueRisk->isNotEmpty()) {
            $totalAtRisk = $overdueRisk->sum('amount');
            $alerts[] = [
                'type'     => FinancialAlertType::OverdueInvoiceRisk->value,
                'severity' => 'high',
                'title'    => 'مبالغ فواتير معرضة للخطر',
                'message'  => "{$overdueRisk->count()} فاتورة بقيمة {$totalAtRisk} ₪ احتمال التحصيل ضعيف",
                'action_link' => route('invoices.index', ['status' => 'overdue']),
            ];
        }

        return $alerts;
    }
}
```

### 4.5 Controller

```php
// app/Modules/Forecast/Http/Controllers/ForecastController.php
class ForecastController extends Controller {

    public function __construct(private CashFlowForecastService $forecast) {}

    public function index(): View {
        $user = auth()->user();
        $currency = $user->currency ?? 'ILS';

        // جلب التوقعات الثلاثة
        $forecast30 = $this->forecast->getForecast(new ForecastRequestDTO($user->id, 30, $currency));
        $forecast60 = $this->forecast->getForecast(new ForecastRequestDTO($user->id, 60, $currency));
        $forecast90 = $this->forecast->getForecast(new ForecastRequestDTO($user->id, 90, $currency));

        return view('forecast.index', compact('forecast30', 'forecast60', 'forecast90'));
    }

    // API للرسم البياني (Chart.js)
    public function chartData(Request $request): JsonResponse {
        $period = (int) $request->input('period', 30);
        $user = auth()->user();

        $result = $this->forecast->getForecast(
            new ForecastRequestDTO($user->id, $period, $user->currency ?? 'ILS')
        );

        return response()->json([
            'labels'  => array_keys($result->dailySnapshots),
            'balance' => array_column($result->dailySnapshots, 'cumulative_balance'),
            'inflow'  => array_column($result->dailySnapshots, 'inflow'),
            'outflow' => array_column($result->dailySnapshots, 'outflow'),
            'risk_level'       => $result->riskLevel->value,
            'confidence_score' => $result->confidenceScore,
            'alerts'           => $result->alerts,
        ]);
    }

    // إعادة توليد التوقع (تجاوز الكاش)
    public function refresh(Request $request): JsonResponse {
        $user = auth()->user();
        $period = (int) $request->input('period', 30);

        $result = $this->forecast->generateForecast(
            new ForecastRequestDTO($user->id, $period, $user->currency ?? 'ILS', useCache: false)
        );

        return response()->json(['success' => true, 'risk_level' => $result->riskLevel->value]);
    }
}
```

---

## 5. واجهة المستخدم (UI/UX)

### 5.1 صفحة التوقع الرئيسية

```
┌─ توقع التدفق النقدي ──────────────────────────────────────────┐
│                                                               │
│  [30 يوم] [60 يوم] [90 يوم]          🔄 تحديث               │
│                                                               │
│  ┌── ملخص الفترة (30 يوم) ─────────────────────────────────┐ │
│  │  رصيدك الحالي    داخل متوقع    خارج متوقع    رصيد نهائي │ │
│  │   25,000 ₪        18,500 ₪      12,000 ₪      31,500 ₪   │ │
│  │                                                          │ │
│  │  🟢 مستوى الخطر: منخفض   |   ثقة التوقع: 82%            │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                               │
│  ⚠️ تنبيهات (1)                                               │
│  └─ 🟠 فاتورة INV-0019 بقيمة 8,000 ₪ احتمال التحصيل ضعيف  │
│                                                               │
│  📊 مخطط التدفق النقدي اليومي                                 │
│  ┌────────────────────────────────────────────────────────┐  │
│  │   [خط الرصيد التراكمي] [أعمدة الدخل/المصروف]          │  │
│  │   === مخطط Chart.js تفاعلي ===                         │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                               │
│  📋 تفاصيل الداخل المتوقع          📋 تفاصيل الخارج المتوقع │
│  ├─ فواتير مستحقة    12,000 ₪     ├─ مصروفات متكررة 8,000 ₪ │
│  ├─ دخل متكرر         4,000 ₪     ├─ سداد ديون      4,000 ₪  │
│  └─ توقع مشاريع       2,500 ₪     └─ مجدول           نقدم    │
└────────────────────────────────────────────────────────────────┘
```

### 5.2 ويدجت لوحة التحكم

```
┌── توقع 30 يوم ────────────────────────┐
│  🟢 وضع مريح          ثقة: 82%        │
│                                       │
│  الرصيد المتوقع: +6,500 ₪            │
│  ──────────────────────────────────── │
│  داخل: 18,500 ₪  |  خارج: 12,000 ₪  │
│                                       │
│  [عرض التفاصيل →]                    │
└───────────────────────────────────────┘
```

### 5.3 تصميم شريط مستوى الخطر

```
🔴 حرج   |  🟠 عالي  |  🟡 متوسط  |  🟢 منخفض
```

---

## 6. Dashboard KPI Widgets

```php
// مقاييس لتضمينها في لوحة التحكم
$widgets = [
    'cash_runway_days'    => 'عدد الأيام قبل نفاد السيولة (إذا توقف الدخل)',
    'overdue_at_risk'     => 'قيمة الفواتير المتأخرة المعرضة لعدم التحصيل',
    'upcoming_payments'   => 'إجمالي المدفوعات خلال 30 يوماً',
    'best_case_balance'   => 'الرصيد في أحسن الأحوال (confidence = 100%)',
    'worst_case_balance'  => 'الرصيد في أسوأ الأحوال (confidence weighted)',
    'monthly_burn_rate'   => 'معدل الإنفاق الشهري الحالي',
];
```

---

## 7. خطط الاشتراك

| الميزة | مجاني | Pro | Business |
|--------|-------|-----|----------|
| توقع 30 يوم | ❌ | ✅ | ✅ |
| توقع 60 يوم | ❌ | ✅ | ✅ |
| توقع 90 يوم | ❌ | ❌ | ✅ |
| تنبيهات العجز | ❌ | ✅ | ✅ |
| تاريخ التوقعات | ❌ | 90 يوم | غير محدود |
| تصدير PDF/CSV | ❌ | ✅ | ✅ |
| ويدجت لوحة التحكم | ❌ | ✅ | ✅ |
| Refresh يدوي | ❌ | 3× يومياً | غير محدود |

**Upgrade Triggers:**
- محاولة الوصول لتوقع 60/90 يوم في مجاني → Modal ترقية مع معاينة
- ظهور تنبيه عجز مع شرح أن التفاصيل تتطلب Pro

---

## 8. Routes

```php
Route::middleware(['auth', 'verified', 'active'])->prefix('forecast')->name('forecast.')->group(function () {
    Route::get('/', [ForecastController::class, 'index'])->name('index');
    Route::get('/chart-data', [ForecastController::class, 'chartData'])->name('chart-data');
    Route::post('/refresh', [ForecastController::class, 'refresh'])->name('refresh');
    Route::get('/alerts', [ForecastController::class, 'alerts'])->name('alerts');
    Route::post('/alerts/{alert}/dismiss', [ForecastController::class, 'dismissAlert'])->name('alerts.dismiss');
});
```

---

## 9. Scheduler

```php
// routes/console.php
$schedule->command('forecast:generate-all')->dailyAt('05:00');
// ↳ يُولّد توقعات 30/60/90 لجميع المستخدمين Pro+
// ↳ يُرسل تنبيهات العجز النقدي المكتشَفة
// Output: storage/logs/forecast.log
```

---

## 10. Events

```php
// app/Modules/Forecast/Events/ForecastGenerated.php
class ForecastGenerated {
    public function __construct(public readonly ForecastResultDTO $result) {}
}

// app/Modules/Forecast/Events/CashShortageAlertTriggered.php
class CashShortageAlertTriggered {
    public function __construct(
        public readonly int $userId,
        public readonly int $daysUntilShortage,
        public readonly float $projectedBalance,
    ) {}
}
```

---

*آخر تحديث: 8 يونيو 2026 — Phase 26 Sprint 2*
