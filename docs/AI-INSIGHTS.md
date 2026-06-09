# محرك الرؤى الذكية — AI Insights Engine (Phase 1)

> وثيقة المواصفات الكاملة | دراهم SaaS Financial Platform  
> الإصدار: 1.0.0 | تاريخ الإنشاء: 8 يونيو 2026 | المرحلة: Phase 26 — Sprint 3

---

## ⚠️ تنبيه مهم

**هذا النظام ليس تكاملاً مع OpenAI أو أي نموذج لغوي خارجي.**  
هو **ذكاء قائم على القواعد (Rule-Based Intelligence)** — يُحلل بيانات المستخدم الفعلية في قاعدة البيانات ويُنتج توصيات ذكية بدون أي تكلفة خارجية.

---

## 1. نظرة عامة (Executive Overview)

**محرك الرؤى الذكية** يُحوّل البيانات المالية الخام في دراهم إلى **توصيات أعمال قابلة للتنفيذ** — بدلاً من أن يرى المستخدم أرقاماً، يرى توصيات: *"عميلك أحمد الأكثر ربحية هذا الشهر — أرسل له عرضاً جديداً"*.

### ما الذي يُميّز هذا النظام؟

```
البيانات الخام → InsightEngineService → رؤى ذكية قابلة للتنفيذ
```

**بدلاً من:** "إجمالي الإيرادات: 45,000 ₪"  
**يقول:** "إيراداتك هذا الشهر أعلى بـ 23% من الشهر الماضي — أفضل أداء منذ 6 أشهر ✅"

**بدلاً من:** "3 فواتير متأخرة"  
**يقول:** "أحمد محمد لديه 3 فواتير متأخرة بإجمالي 12,000 ₪ — آخر دفعة قبل 45 يوماً ⚠️"

---

## 2. أنواع الرؤى (Insight Types)

### 2.1 الرؤى المالية (Financial Insights)

| الرؤية | الوصف | الخوارزمية |
|--------|-------|------------|
| أفضل شهر مالياً | الشهر الذي حقق أعلى صافي دخل | MAX(monthly_net) last 12 months |
| اتجاه الإيرادات | هل الدخل يرتفع أم ينخفض | Linear regression على آخر 6 أشهر |
| معدل الإنفاق | هل المصروفات تتصاعد بشكل غير طبيعي | Z-score على المصروفات الشهرية |
| الفئة الأكثر إنفاقاً | الفئة التي تستهلك أكبر نسبة | TOP 1 FROM categories aggregation |
| هامش الربح الصافي | نسبة الربح من الإيرادات | (income - expenses) / income |
| الموسمية | أفضل/أسوأ أشهر الأداء | Seasonal pattern in monthly data |

### 2.2 رؤى CRM (CRM Insights)

| الرؤية | الوصف | الخوارزمية |
|--------|-------|------------|
| أفضل عميل هذا الشهر | العميل الذي جلب أعلى إيرادات | MAX(invoice.total) per client |
| عملاء يحتاجون متابعة | لم يتم التواصل معهم منذ 30+ يوم | days_since_contact > 30 |
| عملاء في خطر الفقدان | تراجع التعامل + فواتير متأخرة | health_score < 40 |
| أكثر عميل تحويلاً | أعلى نسبة قبول للعروض | accepted_quotes / total_quotes |
| العملاء الجدد هذا الشهر | مقارنة بالأشهر السابقة | COUNT WHERE created_at >= month_start |

### 2.3 رؤى المشاريع (Project Insights)

| الرؤية | الوصف | الخوارزمية |
|--------|-------|------------|
| أكثر مشروع ربحاً | صافي ربح المشاريع النشطة | MAX(income - expense) per project |
| مشروع يخسر المال | مشروع مصروفاته تتجاوز إيراداته | expense > income per project |
| مشاريع بلا نشاط | لا معاملات منذ 30+ يوم | MAX(transaction_date) < 30 days ago |
| أفضل عائد على الاستثمار | إيرادات / مصروفات per project | income / max(expense, 1) DESC |

### 2.4 رؤى الفواتير (Invoice Insights)

| الرؤية | الوصف | الخوارزمية |
|--------|-------|------------|
| متوسط وقت الدفع | كم يوم يأخذ العميل ليدفع | AVG(paid_at - issue_date) |
| عملاء بتأخر متكرر | تأخروا في أكثر من فاتورتين | COUNT(overdue) > 2 per client |
| فواتير معرضة للإهمال | sent منذ 30+ يوم بدون دفع | status=sent AND sent_at < 30 days |
| معدل تحصيل الفواتير | نسبة الفواتير المدفوعة | paid / total invoices |

### 2.5 رؤى الفريق (Team Insights)

| الرؤية | الوصف | الخوارزمية |
|--------|-------|------------|
| أكثر أعضاء الفريق إنجازاً | حسب المشاريع المنتهية | COUNT(project_services per team_member) |
| أعلى تكلفة فريق | العضو الأعلى تكلفة | SUM(service_amount per team_member) |

---

## 3. هيكل بيانات الرؤية (Insight Data Structure)

### 3.1 InsightDTO

```php
// app/Modules/Insights/DTOs/InsightDTO.php
readonly class InsightDTO {
    public function __construct(
        public string $id,               // معرف فريد للرؤية (slug)
        public InsightType $type,        // financial | crm | project | invoice | team
        public InsightSeverity $severity, // info | success | warning | critical
        public string $title,            // عنوان قصير للعرض
        public string $description,      // وصف تفصيلي
        public float $confidence,        // 0.00 − 1.00 — مدى موثوقية الرؤية
        public ?string $actionLabel,     // نص زر الإجراء (nullable)
        public ?string $actionLink,      // رابط الإجراء (nullable)
        public array $data,              // بيانات الرؤية (للعرض)
        public Carbon $generatedAt,
    ) {}
}
```

### 3.2 InsightSeverity Enum

```php
// app/Modules/Insights/Enums/InsightSeverity.php
enum InsightSeverity: string {
    case Info     = 'info';     // 🔵 معلومة مفيدة
    case Success  = 'success';  // 🟢 نتيجة إيجابية
    case Warning  = 'warning';  // 🟡 يستحق الانتباه
    case Critical = 'critical'; // 🔴 يتطلب تدخلاً فورياً

    public function label(): string { /* Arabic */ }
    public function icon(): string { /* Emoji */ }
    public function badgeClass(): string { /* Tailwind CSS class */ }
}
```

### 3.3 InsightType Enum

```php
// app/Modules/Insights/Enums/InsightType.php
enum InsightType: string {
    case Financial = 'financial'; // 💰 مالي
    case CRM       = 'crm';       // 👥 علاقات العملاء
    case Projects  = 'projects';  // 📁 المشاريع
    case Invoices  = 'invoices';  // 📋 الفواتير
    case Team      = 'team';      // 👨‍💼 الفريق

    public function label(): string { /* Arabic */ }
    public function icon(): string { /* Emoji */ }
}
```

---

## 4. InsightEngineService

```php
// app/Modules/Insights/Services/InsightEngineService.php
class InsightEngineService {

    private const CACHE_TTL_MINUTES = 120; // ساعتان

    public function __construct(
        private FinancialInsightAnalyzer $financialAnalyzer,
        private CRMInsightAnalyzer $crmAnalyzer,
        private ProjectInsightAnalyzer $projectAnalyzer,
        private InvoiceInsightAnalyzer $invoiceAnalyzer,
        private TeamInsightAnalyzer $teamAnalyzer,
        private InsightRepository $repository,
    ) {}

    /**
     * جلب جميع الرؤى للمستخدم (مع كاش)
     */
    public function getInsights(int $userId, ?InsightType $filter = null): InsightCollection {
        $cacheKey = "insights.{$userId}." . ($filter?->value ?? 'all');

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($userId, $filter) {
            return $this->generateInsights($userId, $filter);
        });
    }

    /**
     * توليد الرؤى الجديدة
     */
    public function generateInsights(int $userId, ?InsightType $filter = null): InsightCollection {
        $insights = collect();

        $analyzers = match($filter) {
            InsightType::Financial => [$this->financialAnalyzer],
            InsightType::CRM       => [$this->crmAnalyzer],
            InsightType::Projects  => [$this->projectAnalyzer],
            InsightType::Invoices  => [$this->invoiceAnalyzer],
            InsightType::Team      => [$this->teamAnalyzer],
            null => [
                $this->financialAnalyzer,
                $this->crmAnalyzer,
                $this->projectAnalyzer,
                $this->invoiceAnalyzer,
                $this->teamAnalyzer,
            ],
        };

        foreach ($analyzers as $analyzer) {
            try {
                $insights = $insights->merge($analyzer->analyze($userId));
            } catch (\Exception $e) {
                // تجاهل الأخطاء الجزئية — الرؤى الأخرى لا تزال تعمل
                logger()->warning("InsightAnalyzer failed: " . get_class($analyzer), ['error' => $e->getMessage()]);
            }
        }

        // ترتيب: Critical أولاً، ثم Warning، ثم Success، ثم Info
        $sorted = $insights->sortByDesc(fn ($insight) => match($insight->severity) {
            InsightSeverity::Critical => 4,
            InsightSeverity::Warning  => 3,
            InsightSeverity::Success  => 2,
            InsightSeverity::Info     => 1,
        });

        return new InsightCollection($sorted->values()->all());
    }

    /**
     * رؤى الصفحة الرئيسية (أهم 3-5 رؤى)
     */
    public function getDashboardInsights(int $userId): array {
        $all = $this->getInsights($userId);

        return $all->filter(fn ($i) => in_array($i->severity, [
            InsightSeverity::Critical,
            InsightSeverity::Warning,
        ]))->take(3)->values()->all();
    }
}
```

---

## 5. المحللات (Analyzers)

### 5.1 FinancialInsightAnalyzer

```php
// app/Modules/Insights/Analyzers/FinancialInsightAnalyzer.php
class FinancialInsightAnalyzer implements InsightAnalyzerInterface {

    public function analyze(int $userId): array {
        $insights = [];

        // 1. اتجاه الإيرادات
        $revenueTrend = $this->analyzeRevenueTrend($userId);
        if ($revenueTrend) $insights[] = $revenueTrend;

        // 2. شذوذات المصروفات
        $expenseAnomaly = $this->detectExpenseAnomaly($userId);
        if ($expenseAnomaly) $insights[] = $expenseAnomaly;

        // 3. هامش الربح
        $profitMargin = $this->analyzeProfitMargin($userId);
        if ($profitMargin) $insights[] = $profitMargin;

        // 4. الموسمية
        $seasonality = $this->detectSeasonality($userId);
        if ($seasonality) $insights[] = $seasonality;

        return $insights;
    }

    private function analyzeRevenueTrend(int $userId): ?InsightDTO {
        // آخر 3 أشهر مقارنةً بالثلاثة التي قبلها
        $recent = $this->getMonthlyRevenue($userId, 3);
        $prior  = $this->getMonthlyRevenue($userId, 3, offset: 3);

        if ($recent->sum() === 0 && $prior->sum() === 0) return null;

        $change = $prior->sum() > 0
            ? (($recent->sum() - $prior->sum()) / $prior->sum()) * 100
            : null;

        if ($change === null) return null;

        $severity = match(true) {
            $change >= 20  => InsightSeverity::Success,
            $change >= 0   => InsightSeverity::Info,
            $change >= -15 => InsightSeverity::Warning,
            default        => InsightSeverity::Critical,
        };

        return new InsightDTO(
            id: 'financial_revenue_trend',
            type: InsightType::Financial,
            severity: $severity,
            title: $change >= 0 ? 'إيراداتك في تصاعد ✅' : 'إيراداتك في تراجع ⚠️',
            description: $change >= 0
                ? "إيراداتك ارتفعت بنسبة {$change}% مقارنةً بالفترة السابقة — استمر في هذا الأداء"
                : "إيراداتك تراجعت بنسبة " . abs($change) . "% — راجع مصادر دخلك",
            confidence: 0.90,
            actionLabel: $change < 0 ? 'عرض التقارير' : null,
            actionLink: $change < 0 ? route('reports.index') : null,
            data: ['change_percent' => $change, 'recent_total' => $recent->sum(), 'prior_total' => $prior->sum()],
            generatedAt: now(),
        );
    }

    private function detectExpenseAnomaly(int $userId): ?InsightDTO {
        $monthly = $this->getMonthlyExpenses($userId, 6);
        if ($monthly->count() < 3) return null;

        $avg = $monthly->avg();
        $std = $this->standardDeviation($monthly->toArray());
        $lastMonth = $monthly->last();

        $zScore = $std > 0 ? ($lastMonth - $avg) / $std : 0;

        if ($zScore < 1.5) return null; // لا يوجد شذوذ

        $excessAmount = $lastMonth - $avg;

        return new InsightDTO(
            id: 'financial_expense_anomaly',
            type: InsightType::Financial,
            severity: InsightSeverity::Warning,
            title: 'مصروفاتك هذا الشهر أعلى من المعتاد',
            description: "مصروفاتك هذا الشهر أعلى بـ " . number_format($excessAmount) . " ₪ عن متوسطك الشهري — تحقق من المصروفات غير الاعتيادية",
            confidence: min(0.95, 0.70 + ($zScore * 0.10)),
            actionLabel: 'عرض المصروفات',
            actionLink: route('transactions.index', ['type' => 'expense']),
            data: ['last_month' => $lastMonth, 'average' => $avg, 'z_score' => $zScore],
            generatedAt: now(),
        );
    }

    private function standardDeviation(array $values): float {
        $count = count($values);
        if ($count <= 1) return 0;
        $mean = array_sum($values) / $count;
        $sumSquares = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values));
        return sqrt($sumSquares / ($count - 1));
    }
}
```

### 5.2 CRMInsightAnalyzer

```php
// app/Modules/Insights/Analyzers/CRMInsightAnalyzer.php
class CRMInsightAnalyzer implements InsightAnalyzerInterface {

    public function analyze(int $userId): array {
        $insights = [];

        // 1. أفضل عميل هذا الشهر
        $topClient = $this->findTopClientThisMonth($userId);
        if ($topClient) $insights[] = $topClient;

        // 2. عملاء يحتاجون متابعة
        $needFollowUp = $this->findClientsNeedingFollowUp($userId);
        if ($needFollowUp) $insights[] = $needFollowUp;

        // 3. عملاء في خطر
        $atRisk = $this->findAtRiskClients($userId);
        if ($atRisk) $insights[] = $atRisk;

        return $insights;
    }

    private function findTopClientThisMonth(int $userId): ?InsightDTO {
        $topClient = Client::where('user_id', $userId)
            ->withSum(['invoices as month_revenue' => function ($q) {
                $q->where('status', InvoiceStatus::Paid)
                  ->whereMonth('paid_at', now()->month)
                  ->whereYear('paid_at', now()->year);
            }], 'total')
            ->orderByDesc('month_revenue')
            ->first();

        if (!$topClient || $topClient->month_revenue <= 0) return null;

        return new InsightDTO(
            id: 'crm_top_client_month',
            type: InsightType::CRM,
            severity: InsightSeverity::Success,
            title: "أفضل عميل هذا الشهر: {$topClient->name} 🏆",
            description: "جلب {$topClient->name} إيرادات بقيمة " . number_format($topClient->month_revenue) . " ₪ هذا الشهر — فكّر في تقديم عرض تفضيلي للحفاظ على هذا العميل",
            confidence: 0.99,
            actionLabel: 'عرض ملف العميل',
            actionLink: route('crm.clients.show', $topClient->public_id),
            data: ['client_name' => $topClient->name, 'revenue' => $topClient->month_revenue],
            generatedAt: now(),
        );
    }

    private function findClientsNeedingFollowUp(int $userId): ?InsightDTO {
        $clients = Client::where('user_id', $userId)
            ->where('status', ClientStatus::Active)
            ->where(function ($q) {
                $q->whereNull('last_contact_date')
                  ->orWhere('last_contact_date', '<', now()->subDays(30));
            })
            ->count();

        if ($clients === 0) return null;

        return new InsightDTO(
            id: 'crm_needs_followup',
            type: InsightType::CRM,
            severity: InsightSeverity::Warning,
            title: "{$clients} عميل لم يتم التواصل معهم منذ 30+ يوم",
            description: "هناك {$clients} عميل نشط لم تتواصل معهم منذ أكثر من شهر — التواصل المنتظم يرفع معدل الاحتفاظ بالعملاء",
            confidence: 0.95,
            actionLabel: 'عرض العملاء',
            actionLink: route('crm.clients.index', ['filter' => 'needs_contact']),
            data: ['count' => $clients],
            generatedAt: now(),
        );
    }

    private function findAtRiskClients(int $userId): ?InsightDTO {
        $atRisk = Client::where('user_id', $userId)
            ->where('health_score', '<', 40)
            ->where('status', ClientStatus::Active)
            ->count();

        if ($atRisk === 0) return null;

        return new InsightDTO(
            id: 'crm_at_risk_clients',
            type: InsightType::CRM,
            severity: $atRisk >= 3 ? InsightSeverity::Critical : InsightSeverity::Warning,
            title: "{$atRisk} عميل في خطر الفقدان ⚠️",
            description: "{$atRisk} عميل لديهم Health Score أقل من 40% — يحتاجون تدخلاً فورياً قبل خسارتهم",
            confidence: 0.88,
            actionLabel: 'عرض العملاء المعرضين للخطر',
            actionLink: route('crm.clients.index', ['segment' => 'at_risk']),
            data: ['count' => $atRisk],
            generatedAt: now(),
        );
    }
}
```

### 5.3 InvoiceInsightAnalyzer

```php
// app/Modules/Insights/Analyzers/InvoiceInsightAnalyzer.php
class InvoiceInsightAnalyzer implements InsightAnalyzerInterface {

    public function analyze(int $userId): array {
        $insights = [];

        // 1. فواتير متأخرة التحصيل
        $overdueInsight = $this->analyzeOverdueInvoices($userId);
        if ($overdueInsight) $insights[] = $overdueInsight;

        // 2. فواتير مُهملة (sent منذ 30+ يوم)
        $neglected = $this->findNeglectedInvoices($userId);
        if ($neglected) $insights[] = $neglected;

        // 3. معدل التحصيل
        $collectionRate = $this->analyzeCollectionRate($userId);
        if ($collectionRate) $insights[] = $collectionRate;

        // 4. متوسط وقت الدفع
        $avgPaymentTime = $this->analyzeAvgPaymentTime($userId);
        if ($avgPaymentTime) $insights[] = $avgPaymentTime;

        return $insights;
    }

    private function analyzeOverdueInvoices(int $userId): ?InsightDTO {
        $overdue = Invoice::where('user_id', $userId)
            ->where('status', InvoiceStatus::Overdue)
            ->selectRaw('COUNT(*) as count, SUM(total) as total_amount')
            ->first();

        if (!$overdue || $overdue->count === 0) return null;

        return new InsightDTO(
            id: 'invoice_overdue_alert',
            type: InsightType::Invoices,
            severity: $overdue->count >= 3 ? InsightSeverity::Critical : InsightSeverity::Warning,
            title: "{$overdue->count} فاتورة متأخرة بإجمالي " . number_format($overdue->total_amount) . " ₪",
            description: "لديك {$overdue->count} فاتورة تجاوزت تاريخ استحقاقها — أرسل تذكيراً فورياً لتسريع التحصيل",
            confidence: 1.00,
            actionLabel: 'عرض الفواتير المتأخرة',
            actionLink: route('invoices.index', ['status' => 'overdue']),
            data: ['count' => $overdue->count, 'total_amount' => $overdue->total_amount],
            generatedAt: now(),
        );
    }

    private function findNeglectedInvoices(int $userId): ?InsightDTO {
        $neglected = Invoice::where('user_id', $userId)
            ->where('status', InvoiceStatus::Sent)
            ->where('sent_at', '<', now()->subDays(30))
            ->count();

        if ($neglected === 0) return null;

        return new InsightDTO(
            id: 'invoice_neglected',
            type: InsightType::Invoices,
            severity: InsightSeverity::Warning,
            title: "{$neglected} فاتورة مُرسَلة منذ أكثر من 30 يوماً بلا استجابة",
            description: "هذه الفواتير قد تحتاج متابعة مباشرة — إما تذكير أو إعادة تواصل مع العميل",
            confidence: 0.95,
            actionLabel: 'متابعة الفواتير',
            actionLink: route('invoices.index', ['filter' => 'no_response']),
            data: ['count' => $neglected],
            generatedAt: now(),
        );
    }

    private function analyzeCollectionRate(int $userId): ?InsightDTO {
        $stats = Invoice::where('user_id', $userId)
            ->whereIn('status', [InvoiceStatus::Paid, InvoiceStatus::Overdue, InvoiceStatus::Sent])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count
            ')
            ->first();

        if (!$stats || $stats->total < 5) return null; // بيانات غير كافية

        $rate = ($stats->paid_count / $stats->total) * 100;

        $severity = match(true) {
            $rate >= 85 => InsightSeverity::Success,
            $rate >= 65 => InsightSeverity::Info,
            $rate >= 50 => InsightSeverity::Warning,
            default     => InsightSeverity::Critical,
        };

        return new InsightDTO(
            id: 'invoice_collection_rate',
            type: InsightType::Invoices,
            severity: $severity,
            title: "معدل تحصيل فواتيرك: " . round($rate) . "%",
            description: $rate >= 85
                ? "ممتاز! معدل تحصيل " . round($rate) . "% يعني نظام متابعة فعال 💪"
                : "معدل التحصيل " . round($rate) . "% أقل من المستهدف (85%) — فعّل تذكيرات واتساب الآلية",
            confidence: 0.98,
            actionLabel: $rate < 85 ? 'تفعيل التذكيرات' : null,
            actionLink: $rate < 85 ? route('whatsapp.settings') : null,
            data: ['rate' => $rate, 'paid' => $stats->paid_count, 'total' => $stats->total],
            generatedAt: now(),
        );
    }
}
```

### 5.4 ProjectInsightAnalyzer

```php
// app/Modules/Insights/Analyzers/ProjectInsightAnalyzer.php
class ProjectInsightAnalyzer implements InsightAnalyzerInterface {

    public function analyze(int $userId): array {
        $insights = [];

        // 1. أكثر مشروع ربحاً
        $mostProfitable = $this->findMostProfitableProject($userId);
        if ($mostProfitable) $insights[] = $mostProfitable;

        // 2. مشروع خاسر
        $losingProject = $this->findLosingProject($userId);
        if ($losingProject) $insights[] = $losingProject;

        // 3. مشاريع بلا نشاط
        $dormant = $this->findDormantProjects($userId);
        if ($dormant) $insights[] = $dormant;

        return $insights;
    }

    private function findMostProfitableProject(int $userId): ?InsightDTO {
        $projects = Project::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['transactions' => fn ($q) => $q->whereMonth('transaction_date', now()->month)])
            ->get()
            ->map(function ($project) {
                $income = $project->transactions->where('type', 'income')->sum('amount');
                $expense = $project->transactions->where('type', 'expense')->sum('amount');
                return ['project' => $project, 'profit' => $income - $expense, 'income' => $income];
            })
            ->filter(fn ($p) => $p['income'] > 0)
            ->sortByDesc('profit')
            ->first();

        if (!$projects) return null;

        $project = $projects['project'];
        $profit = $projects['profit'];

        return new InsightDTO(
            id: 'project_most_profitable',
            type: InsightType::Projects,
            severity: InsightSeverity::Success,
            title: "أكثر مشاريعك ربحاً هذا الشهر: {$project->name} 🏆",
            description: "مشروع {$project->name} حقق ربحاً صافياً " . number_format($profit) . " ₪ هذا الشهر — ركّز عليه أو اكتب مشاريع مماثلة",
            confidence: 0.99,
            actionLabel: 'عرض المشروع',
            actionLink: route('projects.show', $project->ulid),
            data: ['project_name' => $project->name, 'profit' => $profit],
            generatedAt: now(),
        );
    }

    private function findLosingProject(int $userId): ?InsightDTO {
        $projects = Project::where('user_id', $userId)
            ->where('is_active', true)
            ->with(['transactions'])
            ->get()
            ->map(function ($project) {
                $income = $project->transactions->where('type', 'income')->sum('amount');
                $expense = $project->transactions->where('type', 'expense')->sum('amount');
                return ['project' => $project, 'profit' => $income - $expense, 'income' => $income, 'expense' => $expense];
            })
            ->filter(fn ($p) => $p['expense'] > 0 && $p['profit'] < 0)
            ->sortBy('profit')
            ->first();

        if (!$projects) return null;

        $project = $projects['project'];
        $loss = abs($projects['profit']);

        return new InsightDTO(
            id: 'project_losing_money',
            type: InsightType::Projects,
            severity: InsightSeverity::Critical,
            title: "مشروع {$project->name} يُسجّل خسارة 🔴",
            description: "مصروفات مشروع {$project->name} تتجاوز إيراداته بـ " . number_format($loss) . " ₪ — راجع تسعيره أو تكاليفه",
            confidence: 0.99,
            actionLabel: 'مراجعة المشروع',
            actionLink: route('projects.show', $project->ulid),
            data: ['project_name' => $project->name, 'loss' => $loss],
            generatedAt: now(),
        );
    }
}
```

---

## 6. Controller

```php
// app/Modules/Insights/Http/Controllers/InsightController.php
class InsightController extends Controller {

    public function __construct(private InsightEngineService $engine) {}

    // صفحة الرؤى الكاملة
    public function index(Request $request): View {
        $filter = $request->input('type') ? InsightType::from($request->input('type')) : null;
        $insights = $this->engine->getInsights(auth()->id(), $filter);

        return view('insights.index', [
            'insights'     => $insights,
            'filter'       => $filter,
            'types'        => InsightType::cases(),
            'groupedByType' => $insights->groupBy('type'),
        ]);
    }

    // API للـ Dashboard widget
    public function dashboard(): JsonResponse {
        $insights = $this->engine->getDashboardInsights(auth()->id());
        return response()->json(['insights' => $insights]);
    }

    // إعادة توليد (تجاوز الكاش)
    public function refresh(): JsonResponse {
        Cache::forget("insights.{auth()->id()}.all");
        $insights = $this->engine->generateInsights(auth()->id());
        return response()->json(['success' => true, 'count' => count($insights->all())]);
    }
}
```

---

## 7. واجهة المستخدم (UI)

### 7.1 صفحة الرؤى الرئيسية

```
┌─ رؤى ذكية 💡 ───────────────────────────────────────────────────┐
│  آخر تحديث: منذ 12 دقيقة   [🔄 تحديث]                          │
│                                                                  │
│  [الكل] [💰 مالي] [👥 CRM] [📁 مشاريع] [📋 فواتير] [👨‍💼 فريق] │
│  ──────────────────────────────────────────────────────────────  │
│                                                                  │
│  🔴 حرج (2)                                                      │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ 🔴 مشروع "تطوير موقع X" يُسجّل خسارة                       │ │
│  │ مصروفاته تتجاوز إيراداته بـ 3,500 ₪ — راجع تسعيره         │ │
│  │                          [مراجعة المشروع →]               │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  🟡 يستحق الانتباه (3)                                           │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ 🟡 5 عملاء لم يتم التواصل معهم منذ 30+ يوم                 │ │
│  │                          [عرض العملاء →]                  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  🟢 إيجابي (2)                                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ 🏆 أفضل عميل هذا الشهر: أحمد محمد — 15,000 ₪              │ │
│  │ فكّر في إرسال عرض تفضيلي للحفاظ على هذا العميل            │ │
│  │                          [عرض ملف العميل →]               │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

### 7.2 ويدجت لوحة التحكم (أهم 3 رؤى)

```
┌── رؤى ذكية 💡 ───────────────────────────────────┐
│                                                  │
│  🔴 مشروع يخسر المال — راجع التسعير             │
│  🟡 5 عملاء بحاجة متابعة                        │
│  🟢 إيراداتك ارتفعت 18% هذا الشهر               │
│                                                  │
│  [عرض جميع الرؤى →]                            │
└──────────────────────────────────────────────────┘
```

---

## 8. خطط الاشتراك

| الميزة | مجاني | Pro | Business |
|--------|-------|-----|----------|
| رؤى الفواتير الأساسية | ✅ (2 فقط) | ✅ كاملة | ✅ كاملة |
| رؤى CRM | ❌ | ✅ | ✅ |
| رؤى المشاريع | ❌ | ✅ | ✅ |
| رؤى مالية متقدمة | ❌ | ✅ | ✅ |
| رؤى الفريق | ❌ | ❌ | ✅ |
| ويدجت لوحة التحكم | ❌ | ✅ | ✅ |
| تحديث يدوي | ❌ | 3× يومياً | غير محدود |
| تاريخ الرؤى | ❌ | 30 يوم | 90 يوم |

---

## 9. Routes

```php
Route::middleware(['auth', 'verified', 'active'])->prefix('insights')->name('insights.')->group(function () {
    Route::get('/', [InsightController::class, 'index'])->name('index');
    Route::get('/dashboard', [InsightController::class, 'dashboard'])->name('dashboard');
    Route::post('/refresh', [InsightController::class, 'refresh'])->name('refresh');
});
```

---

## 10. قاعدة البيانات — جدول الرؤى المحفوظة

```sql
CREATE TABLE ai_insights (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ulid            CHAR(26) NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    insight_id      VARCHAR(100) NOT NULL,    -- slug الرؤية
    type            VARCHAR(50) NOT NULL,
    severity        VARCHAR(20) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    description     TEXT NOT NULL,
    confidence      DECIMAL(4,2) NOT NULL,
    action_label    VARCHAR(100) NULL,
    action_link     VARCHAR(500) NULL,
    data            JSON NULL,
    is_read         TINYINT(1) DEFAULT 0,
    is_dismissed    TINYINT(1) DEFAULT 0,
    generated_at    TIMESTAMP NOT NULL,
    expires_at      TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_user_severity (user_id, severity),
    INDEX idx_user_unread (user_id, is_read, is_dismissed),
    INDEX idx_user_insight (user_id, insight_id)
);
```

---

## 11. Scheduler

```php
// routes/console.php
$schedule->command('insights:generate-all')->dailyAt('06:00');
// ↳ يُولّد رؤى جديدة لجميع المستخدمين النشطين
// ↳ يُرسل إشعاراً بالرؤى الحرجة الجديدة
// Output: storage/logs/insights.log
```

---

## 12. مسار التطور — Phase 2 (AI-Powered)

```
Phase 1 (الحالي): Rule-Based Intelligence
├── خوارزميات SQL + PHP
├── لا تكلفة خارجية
├── لا نماذج AI
└── ثقة: 85-99% (بيانات فعلية)

Phase 2 (مستقبلي): ML-Enhanced Intelligence
├── نماذج تنبؤ خفيفة (scikit-learn / TensorFlow Lite)
├── تصنيف سلوك الدفع per-client
├── توقع الإيرادات من الأنماط التاريخية
└── Anomaly Detection متقدم

Phase 3 (بعيد): LLM Integration (اختياري)
├── تحليل نصي للملاحظات والعقود
├── توصيات تسعير ذكية
└── مساعد مالي تفاعلي
```

---

*آخر تحديث: 8 يونيو 2026 — Phase 26 Sprint 3*
