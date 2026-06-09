# Phase 26 — Business Intelligence & Communication Layer

> وثيقة الخارطة الاستراتيجية | دراهم SaaS Financial Platform  
> الإصدار: 1.0.0 | تاريخ الإنشاء: 8 يونيو 2026

---

## 1. المراجعة التنفيذية (Executive Product Review)

### ما الذي أنجزناه؟

بإتمام Phase 25، أصبح دراهم منصةً **مالية + CRM متكاملة** تعمل على الإنتاج بثقة. الأساس راسخ:

- ✅ نظام مالي متكامل (معاملات، مشاريع، ميزانيات، تقارير)
- ✅ نظام CRM متقدم (عملاء، Health Score، Automation، Follow-ups)
- ✅ نظام فواتير + عروض أسعار احترافية
- ✅ Multi-tenant آمن تماماً
- ✅ 54 اختبار Pest — جميعها تعمل

### لماذا Phase 26 الآن؟

Phase 25 أتمّ البناء التشغيلي. Phase 26 يضيف **الطبقة الذكية** — ذكاء الأعمال والتواصل — وهي الطبقة التي تُحوّل دراهم من *"أداة تسجيل"* إلى *"مستشار مالي"*.

**الثلاثة ميزات المُختارة ليست عشوائية:**

| الميزة | ما تحل | الإيرادات المتوقعة |
|--------|--------|-------------------|
| WhatsApp Center | التواصل مع العملاء خارج المنصة | رافع قوي للـ Pro plan |
| Cash Flow Forecast | القلق من المستقبل المالي | ميزة لا بديل عنها للـ Business plan |
| AI Insights | لا يعرف أين يوجّه جهده | يُضاعف قيمة البيانات المُجمَّعة |

---

## 2. تحليل الأثر المعماري (Architecture Impact Analysis)

### ما الذي يتغير في البنية الحالية؟

```
✅ لا تغيير على:
├── BelongsToUser Global Scope — جميع الموديولات الجديدة تستخدمه
├── ULID كمفتاح مسار — مُطبَّق على جميع Models الجديدة
├── DTO → Action → Service — نفس النمط بدون استثناء
├── Event-Driven Design — الأحداث الجديدة تتدفق بنفس الآلية
├── Queue Jobs — WhatsApp Reminders + Forecast generation
└── Scheduler — 3 أوامر جديدة فقط

⚡ إضافات جديدة:
├── 3 موديولات في app/Modules/
├── 3 مجموعات Routes جديدة
├── ~8 Migrations جديدة
├── ~15 Models جديدة (بما فيها enums + DTOs)
├── Cache Layer — للـ Forecast (6h) والـ Insights (2h)
└── Polymorphic Relations — WhatsApp messages → أي كيان
```

### نقاط الارتباط بالكود الحالي

| الموديول الجديد | يرتبط بـ |
|----------------|----------|
| WhatsApp Center | `Invoice`, `Quote`, `Client`, `Project`, `LogClientActivityAction`, `ClientActivityType` |
| Forecast Engine | `Invoice`, `RecurringTransaction`, `Debt`, `Project`, `Transaction` |
| AI Insights | `Invoice`, `Client`, `Project`, `TeamMember`, `Transaction`, `FollowUp` |

**لا كسر في أي API موجود. لا تعديل على controllers موجودة.**  
فقط إضافة زر واتساب في views الموجودة + ويدجت جديد في Dashboard.

---

## 3. استراتيجية التسعير المحدَّثة (SaaS Monetization Strategy)

### خطط الاشتراك المحدَّثة

| الميزة | 🆓 مجاني | ⚡ Pro | 🚀 Business |
|--------|----------|-------|------------|
| **Phase 1-25 (الحالي)** | | | |
| المشاريع | 2 | 10 | غير محدود |
| المعاملات / شهر | 50 | 500 | غير محدود |
| الفواتير | 5 / شهر | غير محدود | غير محدود |
| عروض الأسعار | 3 / شهر | غير محدود | غير محدود |
| CRM عملاء | 10 | 100 | غير محدود |
| التقارير | أساسية | متقدمة | متقدمة + مخصصة |
| **Phase 26 — الجديد** | | | |
| WhatsApp — إرسال يدوي | ❌ | ✅ | ✅ |
| WhatsApp — قوالب مخصصة | ❌ | ✅ 5 قوالب | ✅ غير محدود |
| WhatsApp — تذكيرات آلية | ❌ | ✅ | ✅ |
| توقع التدفق النقدي 30 يوم | ❌ | ✅ | ✅ |
| توقع التدفق النقدي 60 يوم | ❌ | ✅ | ✅ |
| توقع التدفق النقدي 90 يوم | ❌ | ❌ | ✅ |
| رؤى ذكية (أساسية) | ✅ 2 رؤى | ✅ كاملة | ✅ كاملة |
| رؤى ذكية (CRM + المشاريع) | ❌ | ✅ | ✅ |
| رؤى الفريق | ❌ | ❌ | ✅ |
| تنبيهات العجز النقدي | ❌ | ✅ | ✅ |
| تاريخ التوقعات والرؤى | ❌ | 90 يوم | غير محدود |

### محفزات الترقية (Upgrade Triggers) الجديدة

```
مجاني → Pro:
├── محاولة إرسال فاتورة عبر واتساب → "ميزة Pro حصرية"
├── محاولة عرض توقع التدفق النقدي → "رؤية مستقبلك المالي مع Pro"
└── فاتورة متأخرة → "تذكيرات واتساب الآلية تحتاج Pro"

Pro → Business:
├── محاولة توقع 90 يوم → "التخطيط بعيد المدى مع Business"
├── فريق > 5 أعضاء → "رؤى الفريق مع Business"
└── قوالب واتساب > 5 → "قوالب غير محدودة مع Business"
```

### التأثير المالي المتوقع

| المقياس | التقدير |
|---------|---------|
| زيادة معدل تحويل مجاني → Pro | +15% (بسبب WhatsApp feature) |
| زيادة معدل احتفاظ Pro | +20% (بسبب Forecast) |
| زيادة MRR المتوقعة (6 أشهر) | +35% |
| تقليل churn rate | -12% (بسبب Insights الوقائية) |

---

## 4. خارطة الطريق التفصيلية (Detailed Roadmap)

### Sprint 1 — WhatsApp Business Center
**المدة المقدرة:** 2-3 أسابيع | **الأولوية:** P0

#### الأهداف
- إطلاق مركز واتساب كميزة Pro حصرية
- ربط الإرسال بـ CRM Timeline بشكل كامل
- بناء نظام قوالب مرن قابل للتخصيص
- تفعيل التذكيرات الآلية اليومية

#### مهام قاعدة البيانات
```
□ 3 Migrations جديدة
□ WhatsAppTemplateSeeder — 5 قوالب افتراضية
□ إضافة ClientActivityType::WhatsAppSent
□ اختبار الـ Foreign Keys والـ Indexes
```

#### مهام Backend
```
□ 3 Enums + 1 DTO
□ 3 Models (Template, Message, Settings)
□ 3 Actions
□ WhatsAppService + WameProvider
□ WhatsAppController (8 endpoints)
□ 2 Events + 1 Listener
□ 2 Jobs + 1 Command
□ 18 Pest Tests
```

#### مهام Frontend
```
□ صفحة مركز واتساب (index)
□ صفحة القوالب (templates CRUD)
□ صفحة الإعدادات
□ Modal معاينة + إرسال
□ زر واتساب في Invoice/Quote show
□ عنصر واتساب في CRM Timeline
□ إضافة "واتساب" للـ Sidebar Navigation
```

#### فرص التسييل
- WhatsApp Pro: أول محفز ترقية قوي في المنصة
- مستقبلاً: رسوم per-message عند تكامل API حقيقي

---

### Sprint 2 — Cash Flow Forecast Engine
**المدة المقدرة:** 2-3 أسابيع | **الأولوية:** P0

#### الأهداف
- توقع دقيق للتدفق النقدي 30/60/90 يوم
- نظام تنبيهات استباقي للعجز النقدي
- مخطط بصري تفاعلي (Chart.js)
- ويدجت في لوحة التحكم

#### مهام قاعدة البيانات
```
□ Migration: cash_flow_forecasts
□ Migration: financial_alerts
□ Indexes: user_id + period + currency + generated_at
```

#### مهام Backend
```
□ 3 Enums + 3 DTOs
□ 5 Calculators (Invoice, RecurringIn, RecurringOut, Project, Debt)
□ CashFlowForecastService — محور النظام
□ AlertDetector Service
□ ForecastRepository
□ ForecastController (5 endpoints)
□ 2 Events + 1 Command
□ Cache Layer: 6h TTL
□ 20 Pest Tests
```

#### مهام Frontend
```
□ صفحة توقع كاملة مع تبديل 30/60/90
□ Chart.js: خط الرصيد + أعمدة الدخل/المصروف
□ شريط مستوى المخاطر المرئي
□ قسم التنبيهات مع dismiss
□ ويدجت لوحة التحكم
□ تفاصيل الداخل والخارج per-type
```

#### فرص التسييل
- 30/60 يوم: Pro | 90 يوم: Business حصرية
- "Cash Shortage Predicted" → أقوى محفز ترقية نفسي

---

### Sprint 3 — AI Insights Engine
**المدة المقدرة:** 2-3 أسابيع | **الأولوية:** P1

#### الأهداف
- 15+ رؤية ذكية من 5 فئات مختلفة
- ترتيب بالخطورة: Critical → Warning → Success → Info
- ويدجت الرؤى في لوحة التحكم
- Cache ذكي مع refresh يدوي

#### مهام قاعدة البيانات
```
□ Migration: ai_insights
□ Indexes للبحث السريع per user/type/severity
```

#### مهام Backend
```
□ 2 Enums + 1 DTO + 1 Interface
□ 5 Analyzers (Financial, CRM, Invoice, Project, Team)
□ InsightEngineService — orchestrator
□ InsightRepository
□ InsightController (3 endpoints)
□ 1 Command + Cache Layer 2h
□ 20 Pest Tests
```

#### مهام Frontend
```
□ صفحة الرؤى الكاملة مع تصفية per-type
□ بطاقة الرؤية مع severity badge + action button
□ ويدجت لوحة التحكم (أهم 3 رؤى)
□ إضافة "رؤى ذكية" للـ Sidebar Navigation
```

#### فرص التسييل
- 2 رؤى فقط مجاناً → Pro للكل: أبسط upgrade trigger
- رؤى الفريق: Business حصرية

---

## 5. تفاصيل قاعدة البيانات (Database Changes Summary)

### جداول جديدة — Phase 26

| الجدول | الغرض | الحجم المتوقع |
|--------|-------|--------------|
| `whatsapp_settings` | إعدادات واحدة per user | صغير جداً |
| `whatsapp_templates` | قوالب قابلة للتخصيص | صغير |
| `whatsapp_messages` | سجل كل رسالة مُرسَلة | متوسط (ينمو تدريجياً) |
| `cash_flow_forecasts` | نتائج التوقع المحفوظة | متوسط |
| `financial_alerts` | تنبيهات مالية | صغير |
| `ai_insights` | رؤى محفوظة | متوسط |

**الإجمالي: 6 جداول جديدة | ~15 Indexes | ~0 تغييرات على الجداول الموجودة**

---

## 6. خطة تنفيذ Laravel (Laravel Implementation Plan)

### الترتيب الأمثل للتنفيذ

```
Phase 26 Implementation Order:

Week 1-2: Sprint 1 (WhatsApp)
├── Day 1-2: Migrations + Models + Enums
├── Day 3-4: DTOs + Actions + WhatsAppService
├── Day 5-6: Controller + Routes + Events
├── Day 7: Jobs + Command + Scheduler
└── Day 8-10: Views + Testing

Week 3-4: Sprint 2 (Forecast)
├── Day 1-2: Migrations + Enums + DTOs
├── Day 3-5: Calculators (5 classes)
├── Day 6-7: CashFlowForecastService + AlertDetector
├── Day 8: Controller + Routes + Events
└── Day 9-10: Views + Chart.js + Testing

Week 5-6: Sprint 3 (Insights)
├── Day 1-2: Migrations + Enums + DTOs
├── Day 3-6: Analyzers (5 classes)
├── Day 7: InsightEngineService + Controller
├── Day 8: Views + Dashboard Widget
└── Day 9-10: Testing + Integration
```

### أوامر Artisan المطلوبة

```bash
# WhatsApp Center
php artisan make:migration create_whatsapp_settings_table
php artisan make:migration create_whatsapp_templates_table
php artisan make:migration create_whatsapp_messages_table
php artisan make:model WhatsAppTemplate
php artisan make:model WhatsAppMessage
php artisan make:model WhatsAppSettings
php artisan make:controller WhatsApp/WhatsAppController
php artisan make:event WhatsApp/WhatsAppMessagePrepared
php artisan make:listener WhatsApp/LogWhatsAppToTimeline
php artisan make:job WhatsApp/SendWhatsAppReminderJob
php artisan make:command WhatsApp/SendWhatsAppRemindersCommand

# Cash Flow Forecast
php artisan make:migration create_cash_flow_forecasts_table
php artisan make:migration create_financial_alerts_table
php artisan make:model CashFlowForecast
php artisan make:model FinancialAlert
php artisan make:controller Forecast/ForecastController
php artisan make:event Forecast/ForecastGenerated
php artisan make:command Forecast/GenerateAllForecastsCommand

# AI Insights
php artisan make:migration create_ai_insights_table
php artisan make:model AiInsight
php artisan make:controller Insights/InsightController
php artisan make:command Insights/GenerateAllInsightsCommand
```

---

## 7. المخاطر والتخفيف (Risks & Mitigation)

### المخاطر التقنية

| الخطر | الاحتمالية | التأثير | التخفيف |
|-------|------------|---------|---------|
| خوارزمية Forecast تُنتج نتائج خاطئة | متوسط | عالي | Testing شامل + Confidence Score يُشير للبيانات الناقصة |
| WhatsApp wa.me مُحظور في بعض الدول | منخفض | متوسط | WhatsApp Business API كبديل (Phase 2) |
| بطء InsightEngineService على قواعد بيانات كبيرة | متوسط | متوسط | Cache 2h + Scheduler يُشغّل في الليل |
| Forecast Cache قديم يُعطي معلومات خاطئة | منخفض | متوسط | TTL 6h + Refresh يدوي للمستخدم |
| كسر في CRM Timeline عند إضافة WhatsApp activity | منخفض | منخفض | Try-catch في LogWhatsAppToTimeline |

### المخاطر التجارية

| الخطر | التخفيف |
|-------|---------|
| المستخدم لا يفهم "Confidence Score" | شرح بالتول تيب + مقال مساعدة |
| الرؤى غير دقيقة تُسبب قرارات خاطئة | disclaimer: "رؤى مساعدة لا قرارات نهائية" |
| تأخر في إرسال Reminders (Queue) | Queue Worker monitoring + fallback يدوي |

---

## 8. فرص التوسع المستقبلي (Future Expansion Opportunities)

### Phase 27 — المقترحة

```
Sprint 1: WhatsApp Business API Integration
├── تكامل مع Meta Business API
├── إرسال تلقائي بالكامل بدون تدخل يدوي
├── تتبع Delivery + Read Status
├── WhatsApp Templates (البيزنس المعتمدة)
└── استقبال الردود في Inbox داخل دراهم

Sprint 2: AI Insights Phase 2 (ML-Enhanced)
├── نماذج تنبؤ خفيفة (Python microservice)
├── تصنيف سلوك الدفع per-client
├── توقع الإيرادات من الأنماط التاريخية
└── Anomaly Detection متقدم

Sprint 3: Advanced Financial Planning
├── أهداف ادخار + تتبع تقدم
├── ميزانية ذكية مع توصيات تعديل
├── مقارنة مع متوسط القطاع (Benchmarking)
└── تقارير ضريبية مبسطة
```

### Phase 28+ — طويل المدى

```
□ REST API عام (Laravel Sanctum) — للتكامل مع أدوات خارجية
□ تطبيق Flutter (iOS + Android)
□ Open Banking API (ربط بالبنوك)
□ Zapier / n8n / Make.com Integration
□ تقارير ضريبية معتمدة
□ Multi-user / Teams
□ White-label للوكالات
```

---

## 9. ملخص تنفيذي — الجاهزية للتنفيذ

### ✅ هل البنية الحالية تدعم هذه الميزات؟ نعم

البنية الحالية مبنية بشكل صحيح 100% — جميع الميزات الجديدة تُضاف فوقها بدون أي كسر:

- Multi-tenant جاهز ✅
- Queue + Scheduler جاهز ✅
- Event System جاهز ✅
- BelongsToUser لكل Model جديد ✅
- DTO + Action + Service pattern متبَع ✅

### الأولوية النهائية للتنفيذ

```
1️⃣ WhatsApp Business Center (Sprint 1) — الأعلى تأثيراً على الإيرادات
2️⃣ Cash Flow Forecast (Sprint 2) — الأعلى قيمة للمستخدم
3️⃣ AI Insights (Sprint 3) — الأقوى في التمايز التنافسي
```

### التقدير الزمني الإجمالي

| العمل | الوقت المقدَّر |
|-------|--------------|
| Phase 26 Sprint 1 (WhatsApp) | 2-3 أسابيع |
| Phase 26 Sprint 2 (Forecast) | 2-3 أسابيع |
| Phase 26 Sprint 3 (Insights) | 2-3 أسابيع |
| **المجموع** | **6-9 أسابيع** |

---

## 10. الوثائق ذات الصلة

| الوثيقة | الوصف |
|---------|-------|
| `docs/WHATSAPP-CENTER.md` | المواصفة الكاملة للـ Sprint 1 |
| `docs/CASH-FLOW-FORECAST.md` | المواصفة الكاملة للـ Sprint 2 |
| `docs/AI-INSIGHTS.md` | المواصفة الكاملة للـ Sprint 3 |
| `docs/ARCHITECTURE.md` | بنية النظام المحدَّثة (v3.0.0) |
| `docs/TASKS.md` | قائمة مهام Phase 26 التفصيلية |
| `docs/PROJECT.md` | وصف المشروع العام |

---

*دراهم — من منصة تسجيل إلى مستشار مالي ذكي.*  
*Phase 26 — Business Intelligence & Communication Layer*  
*آخر تحديث: 8 يونيو 2026*
