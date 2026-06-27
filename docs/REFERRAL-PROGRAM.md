# REFERRAL-PROGRAM.md
## نظام الإحالات — دراهم | الوثيقة الرسمية المعتمدة

> **الإصدار:** 2.2
> **التاريخ:** 27 يونيو 2026
> **الحالة:** ✅ Approved For Implementation
> **الجمهور:** الهندسة · التسويق · الإدارة
> **يُلغي:** النسخة 2.1 بتاريخ 27/6/2026

---

## التغييرات من الإصدار 2.1 — Changes From v2.1

| # | التغيير | القسم المتأثر |
|---|---------|-------------|
| 1 | إضافة Database Compatibility Requirements (MySQL 8.0.16+ / MariaDB 10.4+) | §3، §18 (جديد) |
| 2 | تعزيز أمان referral_visitor_token Cookie (Secure + HttpOnly + SameSite=lax) | §3.2، §4.1 |
| 3 | نقل FraudResult من DTOs/ إلى ValueObjects/ | §5.5، §6، §17 |
| 4 | تحديث منطق total_converted في Reconciliation ليشمل 'pending' للترقية الفورية | §15 |

---

## التغييرات من الإصدار 2.0 — Changes From v2.0

| # | التغيير | القسم المتأثر |
|---|---------|-------------|
| 1 | إزالة جميع MySQL ENUMs واستبدالها بـ VARCHAR + CHECK Constraints | §3 |
| 2 | إصلاح visitor_token: ULID Cookie بدلًا من SHA-256 | §3.2، §4.1 |
| 3 | توضيح SubscriptionActivated: إضافة `isFirstActivation` لمنع عمولات التجديد | §4.2، §8.1 |
| 4 | فرض حساب إحالة واحد لكل مستخدم: `UNIQUE INDEX uidx_affiliates_user` | §3.1، §10 |
| 5 | إضافة Reconciliation Command يومي لإعادة حساب الـ Aggregates | §15 (جديد) |
| 6 | Queue مستقلة `referrals` لعمليات الإحالة + قسم Queue Architecture | §4.2، §16 (جديد) |
| 7 | نظام مكافحة الاحتيال: Fraud Prevention Rules + FraudDetectionService | §17 (جديد) |
| 8 | تحديث رأس الوثيقة إلى v2.1 Final Production Candidate | §Header |

---

## جدول المحتويات

1. [الهدف والسياق](#1-الهدف-والسياق)
2. [المعمارية العامة](#2-المعمارية-العامة)
3. [قاعدة البيانات — Database Schema](#3-قاعدة-البيانات--database-schema)
4. [تدفق الأحداث — Event Flow](#4-تدفق-الأحداث--event-flow)
5. [طبقة الخدمات — Service Layer](#5-طبقة-الخدمات--service-layer)
6. [هيكل الملفات — Laravel File Structure](#6-هيكل-الملفات--laravel-file-structure)
7. [مستويات العمولة — Commission Tiers](#7-مستويات-العمولة--commission-tiers)
8. [التكامل مع الأنظمة الحالية](#8-التكامل-مع-الأنظمة-الحالية)
9. [لوحة تحكم المسوّق](#9-لوحة-تحكم-المسوق)
10. [قواعد البرنامج وحدوده](#10-قواعد-البرنامج-وحدوده)
11. [خارطة التنفيذ — Roadmap](#11-خارطة-التنفيذ--roadmap)
12. [خطة الـ Migration](#12-خطة-الـ-migration)
13. [QA Checklist](#13-qa-checklist)
14. [ملاحظات التوسّع المستقبلي](#14-ملاحظات-التوسّع-المستقبلي)
15. [Referral Aggregates Reconciliation](#15-referral-aggregates-reconciliation)
16. [Queue Architecture](#16-queue-architecture)
17. [Fraud Prevention](#17-fraud-prevention)
18. [Database Compatibility Requirements](#18-database-compatibility-requirements)
19. [Implementation Approval](#19-implementation-approval)

---

## 1. الهدف والسياق

### المشكلة
دراهم تستهدف المستقلين في السوق العربي — وهو سوق يعتمد على الثقة والتوصية الشخصية أكثر من الإعلانات المدفوعة. معدل التحويل من `free → paid` منخفض (حاليًا أقل من 6%)، والحاجة ماسّة لقنوات نمو عضوي.

### الحل
برنامج إحالات مدفوع يُمكّن **المسوّقين والمستخدمين الراضين** من الترويج مقابل عمولة مالية عند كل اشتراك مدفوع يأتي عبرهم.

### الأهداف
- رفع المشتركين المدفوعين إلى 50 خلال 6 أشهر
- خفض تكلفة اكتساب العميل (CAC) مقارنةً بالإعلانات
- بناء شبكة مسوّقين: مستقلون، كوتش، صفحات تواصل اجتماعي

---

## 2. المعمارية العامة

### المبادئ الحاكمة
النظام يلتزم بالمعمارية القياسية لدراهم:

```
Controller (رفيع جداً)
    └─► Service (أوركسترا)
            └─► Action (منطق واحد محدد)
                    └─► event(new SubscriptionActivated($subscription, isFirstActivation: true))
                                └─► Listener ($afterCommit = true, ShouldQueue, ->onQueue('referrals'))
                                        └─► FraudDetectionService::check()
                                                └─► Action (تسجيل العمولة)
```

### مخطط المعمارية

```
┌──────────────────────────────────────────────────────────────────────┐
│                       app/Modules/Referral/                          │
│                                                                      │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────────────────┐ │
│  │   Enums/     │   │    DTOs/     │   │        Actions/          │ │
│  │AffiliateStatus│  │CreateAffili..│   │  CreateAffiliateAction   │ │
│  │CommissionStat│  │ReferralClick.│   │  RecordClickAction       │ │
│  │AffiliateTier │  │CommissionDTO │   │  CreateCommissionAction  │ │
│  │PayoutMethod  │  │              │   │  ApproveCommissionAction │ │
│  │PayoutStatus  │  └──────────────┘   │  RequestPayoutAction     │ │
│  └──────────────┘                     │  UpgradeTierAction       │ │
│                                       └──────────────────────────┘ │
│  ┌───────────────────────────────┐                                  │
│  │          Services/            │   ┌──────────────────────────┐  │
│  │       ReferralService         │   │       Listeners/         │  │
│  │   FraudDetectionService ←جديد│   │ CreateReferralCommission │  │
│  └───────────────────────────────┘   │  $afterCommit = true     │  │
│                                      │  ShouldQueue             │  │
│  ┌───────────────────────────────┐   │  ->onQueue('referrals')  │  │
│  │      Commands/ ← جديد         │   └──────────────────────────┘  │
│  │  ReconcileReferralAggregates  │                                  │
│  └───────────────────────────────┘                                  │
└──────────────────────────────────────────────────────────────────────┘
```

### المعرّفات — ULID أساس النظام
جميع جداول الموديول تستخدم **ULID** كـ Primary Key (مثل `Subscription`, `Transaction`, `Client`)، مع `display_code` اختياري لأغراض التسويق فقط:

```
/ref/{affiliate_ulid}          ← المرجع الداخلي والخارجي الأساسي
/ref/AHMED2026                 ← display_code (تسويقي فقط، redirect → ULID route)
```

---

## 3. قاعدة البيانات — Database Schema

> ### ⚠️ قرار معماري: لا MySQL ENUMs
>
> دراهم يتبع مبدأ **No MySQL ENUMs in production systems**.
>
> **الأسباب:**
> - **Zero-downtime schema changes:** تعديل ENUM يتطلب إعادة بناء الجدول (`ALTER TABLE ... MODIFY COLUMN`) مما يُسبب قفلًا كاملًا (full table lock) على الجداول الكبيرة.
> - **تجنب إعادة بناء الجداول:** إضافة قيمة جديدة لـ ENUM = `ALTER TABLE` = downtime محتمل في Production.
> - **الاتساق مع معمارية CRM الحالية:** جميع حالات الموديولات الحالية (`ClientStatus`, `FollowUpStatus`, إلخ) مُعرَّفة كـ PHP Backed Enums ومخزَّنة كـ VARCHAR.
>
> **البديل المعتمد:** `VARCHAR(30) NOT NULL + CHECK constraint + PHP Backed Enum`
>
> ```sql
> -- ❌ ممنوع
> status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending'
>
> -- ✅ المعتمد
> status VARCHAR(20) NOT NULL DEFAULT 'pending'
>     CHECK (status IN ('pending','active','suspended'))
> ```
>
> ⚠️ **متطلب بيئة التشغيل:** CHECK constraints فعّالة فقط على **MySQL 8.0.16+** أو **MariaDB 10.4+**. راجع §18 للتفاصيل الكاملة.

---

### 3.1 جدول `affiliates`

```sql
CREATE TABLE affiliates (
    id               CHAR(26)      NOT NULL PRIMARY KEY,   -- ULID
    user_id          CHAR(26)      NULL,                   -- ربط بحساب دراهم (اختياري)
    name             VARCHAR(100)  NOT NULL,
    email            VARCHAR(150)  NOT NULL UNIQUE,
    whatsapp         VARCHAR(20)   NULL,
    display_code     VARCHAR(50)   UNIQUE NULL,            -- تسويقي فقط (AHMED2026)
    commission_rate  DECIMAL(5,2)  NOT NULL DEFAULT 30.00,
    status           VARCHAR(20)   NOT NULL DEFAULT 'pending'
                         CHECK (status IN ('pending','active','suspended')),
    tier             VARCHAR(20)   NOT NULL DEFAULT 'standard'
                         CHECK (tier IN ('standard','silver','gold','platinum')),
    payout_method    VARCHAR(20)   NULL
                         CHECK (payout_method IN ('bank','whatsapp','credit')),
    payout_details   JSON          NULL,                   -- بيانات حساب الصرف
    total_referrals  INT UNSIGNED  NOT NULL DEFAULT 0,     -- Denormalized — راجع §15
    total_converted  INT UNSIGNED  NOT NULL DEFAULT 0,     -- Denormalized — راجع §15
    total_earned     DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- Denormalized — راجع §15
    total_paid       DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- Denormalized — راجع §15
    notes            TEXT          NULL,                   -- ملاحظات الأدمن
    approved_at      TIMESTAMP     NULL,
    suspended_at     TIMESTAMP     NULL,
    created_at       TIMESTAMP     NOT NULL,
    updated_at       TIMESTAMP     NOT NULL,

    CONSTRAINT fk_affiliates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

    -- قاعدة: User → One Affiliate Account only (راجع §10)
    UNIQUE INDEX uidx_affiliates_user         (user_id),
    INDEX        idx_affiliates_status        (status),
    INDEX        idx_affiliates_display_code  (display_code)
);
```

> **`balance` محسوب دائمًا:** `balance = total_earned - total_paid` — لا تُخزَّن مباشرة تجنّبًا للتباين. راجع §15 لآلية إعادة الحساب اليومية.

---

### 3.2 جدول `referral_clicks`

يُسجَّل كل زيارة لرابط إحالة ويُربط بالتسجيل/الاشتراك لاحقًا:

```sql
CREATE TABLE referral_clicks (
    id               CHAR(26)      NOT NULL PRIMARY KEY,   -- ULID
    affiliate_id     CHAR(26)      NOT NULL,
    visitor_token    CHAR(26)      NOT NULL,               -- ULID ثابت من Cookie (راجع §4.1)
    ip_address       VARCHAR(45)   NULL,                   -- IPv4/IPv6 — للكشف عن الاحتيال فقط
    user_agent       TEXT          NULL,
    landing_page     VARCHAR(500)  NULL,                   -- URL كامل عند الزيارة
    converted_at     TIMESTAMP     NULL,                   -- وقت التسجيل/الاشتراك
    created_at       TIMESTAMP     NOT NULL,

    CONSTRAINT fk_clicks_affiliate FOREIGN KEY (affiliate_id) REFERENCES affiliates(id) ON DELETE CASCADE,
    INDEX idx_clicks_affiliate  (affiliate_id),
    INDEX idx_clicks_visitor    (visitor_token),
    INDEX idx_clicks_ip         (ip_address),              -- للكشف عن Click Spam (§17)
    INDEX idx_clicks_converted  (converted_at)
);
```

> ### visitor_token — آلية الـ ULID Cookie (v2.2 Secure)
>
> **v2.2:** الـ `visitor_token` ULID ثابت يعيش في Cookie مؤمَّنة كاملة:
>
> ```php
> // TrackReferralCode Middleware
> if (! $request->hasCookie('referral_visitor_token')) {
>     $token = (string) Str::ulid();
>
>     Cookie::queue(
>         Cookie::make(
>             name:     'referral_visitor_token',
>             value:    $token,
>             minutes:  60 * 24 * 60,                 // 60 يومًا
>             path:     '/',
>             domain:   config('session.domain'),      // يشمل النطاقات الفرعية
>             secure:   true,                          // HTTPS فقط
>             httpOnly: true,                          // لا JavaScript
>             sameSite: 'lax'                          // حماية CSRF مع redirect طبيعي
>         )
>     );
> } else {
>     $token = $request->cookie('referral_visitor_token');
> }
> ```
>
> #### خصائص الـ Cookie
>
> | الخاصية | القيمة | السبب |
> |---------|--------|-------|
> | **Secure** | `true` | إرسال عبر HTTPS فقط — يمنع الاعتراض على HTTP |
> | **HttpOnly** | `true` | لا يُقرأ عبر `document.cookie` — يُقلَّل خطر XSS |
> | **SameSite** | `lax` | يسمح بالتنقل الطبيعي (redirect من روابط خارجية) مع حماية CSRF |
> | **Duration** | `60 days` | نافذة attribution كافية لمعظم القرارات الشرائية |
> | **Domain** | `config('session.domain')` | يشمل النطاقات الفرعية — يحافظ على الإحالة عبر subdomain |
>
> #### لماذا HttpOnly ضروري؟
> Cookie الإحالة تحمل `visitor_token` مرتبطًا بمسوّق. قراءتها عبر JavaScript تُتيح لسكريبت خارجي (XSS) نسبَ إحالة وهمية لمسوّق أو التلاعب بـ attribution. HttpOnly يُغلق هذا الباب تمامًا.
>
> #### لماذا لا SHA-256(ip + user_agent + date)؟
> - IP يتغيّر (موبايل → واي فاي)
> - User Agent يتغيّر عند تحديث المتصفح
> - تصادم غير مقصود بين مستخدمين مختلفين على نفس الشبكة (NAT)
>
> #### خصائص visitor_token
>
> | الخاصية | القيمة |
> |---------|--------|
> | النوع | ULID — 26 حرف |
> | مدة الحياة | 60 يومًا |
> | مستقل عن IP | ✅ |
> | مستقل عن User Agent | ✅ |
> | مستقل عن التاريخ | ✅ |
> | قابل للقراءة بـ JS | ❌ (HttpOnly) |
> | يتغيّر عند مسح الـ Cookies | ✅ طبيعي |

---

### 3.3 تعديل جدول `users`

```sql
ALTER TABLE users
    ADD COLUMN referred_by_affiliate_id CHAR(26)   NULL AFTER remember_token,
    ADD COLUMN referral_click_id        CHAR(26)   NULL AFTER referred_by_affiliate_id,
    ADD COLUMN referral_attributed_at   TIMESTAMP  NULL AFTER referral_click_id,

    ADD CONSTRAINT fk_users_affiliate FOREIGN KEY (referred_by_affiliate_id)
        REFERENCES affiliates(id) ON DELETE SET NULL,

    ADD CONSTRAINT fk_users_click FOREIGN KEY (referral_click_id)
        REFERENCES referral_clicks(id) ON DELETE SET NULL;
```

---

### 3.4 جدول `referral_commissions`

```sql
CREATE TABLE referral_commissions (
    id                  CHAR(26)      NOT NULL PRIMARY KEY,   -- ULID
    affiliate_id        CHAR(26)      NOT NULL,
    subscription_id     CHAR(26)      NOT NULL,               -- FK → subscriptions.id
    referred_user_id    CHAR(26)      NOT NULL,               -- FK → users.id
    amount              DECIMAL(10,2) NOT NULL,               -- قيمة العمولة بالدولار
    currency            CHAR(3)       NOT NULL DEFAULT 'USD',
    rate                DECIMAL(5,2)  NOT NULL,               -- النسبة وقت الاشتراك
    status              VARCHAR(20)   NOT NULL DEFAULT 'pending'
                            CHECK (status IN (
                                'pending',    -- قيد الانتظار (7 أيام)
                                'approved',   -- معتمدة، تنتظر الدفع
                                'paid',       -- مدفوعة
                                'rejected',   -- مرفوضة (احتيال أو خطأ)
                                'cancelled'   -- ملغاة (الاشتراك استُرد)
                            )),
    trigger_source      VARCHAR(30)   NOT NULL
                            CHECK (trigger_source IN ('togo_callback','manual_admin')),
    subscription_amount DECIMAL(10,2) NOT NULL,               -- قيمة الاشتراك الأصلية
    subscription_plan   VARCHAR(20)   NOT NULL,               -- pro / business
    subscription_cycle  VARCHAR(10)   NOT NULL
                            CHECK (subscription_cycle IN ('monthly','annual')),
    fraud_flagged       TINYINT(1)    NOT NULL DEFAULT 0,     -- علامة الاحتيال (§17)
    approved_at         TIMESTAMP     NULL,
    paid_at             TIMESTAMP     NULL,
    notes               TEXT          NULL,
    created_at          TIMESTAMP     NOT NULL,
    updated_at          TIMESTAMP     NOT NULL,

    CONSTRAINT fk_commissions_affiliate    FOREIGN KEY (affiliate_id)     REFERENCES affiliates(id),
    CONSTRAINT fk_commissions_subscription FOREIGN KEY (subscription_id)  REFERENCES subscriptions(id),
    CONSTRAINT fk_commissions_user         FOREIGN KEY (referred_user_id) REFERENCES users(id),

    UNIQUE INDEX uidx_commissions_subscription  (subscription_id),  -- عمولة واحدة لكل اشتراك
    INDEX        idx_commissions_affiliate       (affiliate_id),
    INDEX        idx_commissions_status          (status),
    INDEX        idx_commissions_fraud           (fraud_flagged)
);
```

---

### 3.5 جدول `referral_payouts`

```sql
CREATE TABLE referral_payouts (
    id               CHAR(26)      NOT NULL PRIMARY KEY,   -- ULID
    affiliate_id     CHAR(26)      NOT NULL,
    amount           DECIMAL(10,2) NOT NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    method           VARCHAR(20)   NOT NULL
                         CHECK (method IN ('bank','whatsapp','credit')),
    status           VARCHAR(20)   NOT NULL DEFAULT 'requested'
                         CHECK (status IN ('requested','processing','paid','rejected')),
    admin_notes      TEXT          NULL,
    requested_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at     TIMESTAMP     NULL,
    created_at       TIMESTAMP     NOT NULL,
    updated_at       TIMESTAMP     NOT NULL,

    CONSTRAINT fk_payouts_affiliate FOREIGN KEY (affiliate_id) REFERENCES affiliates(id),
    INDEX idx_payouts_affiliate  (affiliate_id),
    INDEX idx_payouts_status     (status)
);
```

---

### 3.6 مخطط العلاقات

```
affiliates (ULID)
    │  [UNIQUE user_id → One Account Per User]
    │
    ├──[has many]── referral_clicks (ULID)
    │                    │  [visitor_token: ULID Cookie, 60 days]
    │                    └──[tracked via]── users.referral_click_id
    │
    ├──[has many]── referral_commissions (ULID)
    │                    ├── subscription_id ──► subscriptions (ULID)
    │                    └── referred_user_id ──► users (ULID)
    │
    ├──[has many]── referral_payouts (ULID)
    │
    └──[belongs to optionally]── users (ULID)

users
    ├── referred_by_affiliate_id ──► affiliates (ULID)
    └── referral_click_id ──────────► referral_clicks (ULID)
```

---

## 4. تدفق الأحداث — Event Flow

### 4.1 تدفق Attribution (تتبع الإحالة)

```
[1] المستخدم يضغط /ref/{ulid} أو /ref/{display_code}
         │
         ▼
[2] Middleware: TrackReferralCode
    ├── يحل display_code → affiliate.id (إن كان كوداً)
    ├── FraudDetectionService::detectClickSpam($ip) — يتحقق من حد 20 click/IP/day
    ├── إذا لم يوجد Cookie 'referral_visitor_token':
    │       يُنشئ ULID جديد → Cookie::make(secure:true, httpOnly:true, sameSite:'lax', 60 days)
    │   إذا وُجد:
    │       يقرأ الـ ULID الموجود (HttpOnly — غير مقروء من JS)
    ├── يُنشئ ReferralClick جديداً في DB (visitor_token = ULID من Cookie)
    ├── يُخزَّن affiliate_id في Session
    └── يُعيد التوجيه لـ / أو landing_page
         │
         ▼
[3] المستخدم يُسجَّل في /register
    ├── RegisterUserController يقرأ ref_affiliate_id من Session
    │   (Cookie 'referral_visitor_token' تبقى للتحقق فقط)
    ├── FraudDetectionService::detectDuplicateAccounts($request)
    ├── يستدعي ReferralService::attributeRegistration($user, $affiliateId, $clickId)
    │    ├── Self-referral check → إذا انطبق: يُسجَّل في logs ويُرفض
    │    ├── Affiliate status = active check
    │    ├── يُحدِّث: users.referred_by_affiliate_id + referral_click_id + referral_attributed_at
    │    ├── يُحدِّث: affiliates.total_referrals++
    │    └── يُحدِّث: referral_clicks.converted_at = now()
    └── يُمسح الـ Session (Cookie تبقى 60 يومًا للتحقق من المستقبل)
```

---

### 4.2 تدفق إنشاء العمولة (Event-Driven)

```
[1] SubscriptionService::activatePlan()
    └─► DB::transaction {
            subscription = Subscription::create(...)
            user->update(...)
            event(new SubscriptionActivated(
                subscription:      $subscription,
                isFirstActivation: true,       ← تفعيل أول، ليس تجديدًا
                triggerSource:     'togo_callback'
            ))
        }
         │
         ▼
[2] Queue: 'referrals' — CreateReferralCommission Listener
    ├── implements ShouldQueue
    ├── public bool $afterCommit = true
    ├── public string $queue = 'referrals'
    └── handle(SubscriptionActivated $event)
         │
         ├── [GUARD] if (! $event->isFirstActivation) return;  ← يمنع عمولات التجديد
         ├── [GUARD] هل المستخدم مُحال من مسوّق؟
         ├── [GUARD] هل سبق تسجيل عمولة لهذا subscription_id؟
         │
         ├── FraudDetectionService::detectSuspiciousConversions($affiliate, $user)
         │    └── إذا مشبوه: commission.fraud_flagged = 1, status = 'pending' لـ 7 أيام
         │
         ├── يستدعي CreateReferralCommissionAction::execute(CommissionDTO)
         │    ├── ينشئ referral_commissions (status='pending', fraud_flagged=0/1)
         │    ├── يُحدِّث affiliates.total_earned += commission_amount
         │    ├── يُحدِّث affiliates.total_converted++
         │    └── يستدعي UpgradeAffiliateTierAction::execute($affiliate)
         │
         └── يُرسل NewCommissionEarnedNotification للمسوّق
```

### 4.3 لماذا `afterCommit = true`؟

```
بدون afterCommit:
    Transaction يبدأ
    ├── subscription يُنشأ في DB
    ├── event يُطلَق → Listener يُضاف للـ Queue فوراً
    │    └── Worker يعمل → يحاول قراءة subscription → race condition محتمل
    └── Transaction يكتمل

مع afterCommit = true:
    Transaction يبدأ
    ├── subscription يُنشأ في DB
    ├── event يُطلَق → Listener ينتظر الـ Commit
    └── Transaction يكتمل ✓
         └── الآن Listener يُضاف للـ Queue → subscription موجود بشكل مؤكد ✓
```

### 4.4 التمييز بين `isFirstActivation` و `isRenewal`

```
activatePlan() [تفعيل أول — من Togo أو Admin]:
    event(new SubscriptionActivated(
        subscription:      $subscription,
        isFirstActivation: true,
        triggerSource:     'togo_callback' | 'manual_admin'
    ))
    → CreateReferralCommission يعمل ✓

SubscriptionService::renewPlan() [تجديد — مستقبلاً]:
    event(new SubscriptionRenewed(...))    ← حدث مختلف، لا يُطلق CreateReferralCommission
    — أو —
    event(new SubscriptionActivated(
        subscription:      $subscription,
        isFirstActivation: false,          ← Listener يُرجع فوراً
    ))
    → CreateReferralCommission يتحقق → return; ✓
```

---

## 5. طبقة الخدمات — Service Layer

### 5.1 `ReferralService` — أوركسترا النظام

```php
namespace App\Modules\Referral\Services;

/**
 * ReferralService — المنسّق الرئيسي لنظام الإحالات
 *
 * مسؤولياته:
 *  1. حل معرّف المسوّق (ULID أو display_code)
 *  2. ربط التسجيل بالمسوّق (Attribution)
 *  3. حساب العمولة وتحديد التier
 *  4. التحقق من شروط الأهلية
 *
 * لا يُنشئ العمولة مباشرة — يُفوَّض ذلك للـ Listener عبر SubscriptionActivated
 */
class ReferralService
{
    public function resolveAffiliate(string $identifier): ?Affiliate;
        // يحاول أولاً: ULID (26 حرف)
        // ثانياً: display_code
        // يُرجع null إن لم يجد أو كان status != 'active'

    public function attributeRegistration(
        User   $user,
        string $affiliateId,
        string $clickId,
    ): void;
        // FraudDetectionService::detectSelfReferral() → log + reject إن انطبق
        // يتحقق affiliate.status = 'active'
        // يُحدّث users + referral_clicks + affiliates.total_referrals

    public function resolveCommissionRate(Affiliate $affiliate): float;
        // يُرجع النسبة حسب total_converted (راجع §7)

    public function calculateCommission(float $subscriptionAmount, float $rate): float;
        // round($subscriptionAmount * $rate / 100, 2)

    public function canRequestPayout(Affiliate $affiliate): bool;
        // balance >= 20 USD AND لا يوجد طلب صرف بحالة 'requested' أو 'processing'
}
```

### 5.2 `CreateReferralCommissionAction`

```php
namespace App\Modules\Referral\Actions\Commission;

/**
 * ينشئ سجل عمولة واحد لاشتراك واحد.
 * يُستدعى حصراً من CreateReferralCommission Listener.
 *
 * القيود:
 *  - UNIQUE INDEX على subscription_id يمنع التكرار على مستوى DB
 *  - يُسجَّل fraud_flagged إذا أشارت FraudDetectionService
 *  - العمولات المشبوهة تبقى بحالة 'pending' لمدة 7 أيام قبل الموافقة التلقائية
 */
class CreateReferralCommissionAction
{
    public function execute(CreateCommissionDTO $dto): ReferralCommission;
}
```

### 5.3 `UpgradeAffiliateTierAction`

```php
namespace App\Modules\Referral\Actions\Commission;

/**
 * يُقيّم Tier المسوّق بعد كل اشتراك جديد ويُحدّثه إن استحق.
 * يُستدعى من CreateReferralCommissionAction بعد تسجيل العمولة.
 */
class UpgradeAffiliateTierAction
{
    public function execute(Affiliate $affiliate): void
    {
        $resolved = $this->resolveNewTier($affiliate->total_converted);

        if ($affiliate->tier === $resolved['tier']->value) {
            return; // لا تغيير
        }

        $affiliate->update([
            'tier'            => $resolved['tier']->value,
            'commission_rate' => $resolved['rate'],
        ]);

        // إشعار الترقية — اختياري في Phase 1
        $affiliate->user?->notify(new TierUpgradedNotification($resolved['tier']));
    }

    private function resolveNewTier(int $totalConverted): array
    {
        return match(true) {
            $totalConverted >= 100 => ['tier' => AffiliateTier::Platinum, 'rate' => 45.00],
            $totalConverted >= 30  => ['tier' => AffiliateTier::Gold,     'rate' => 40.00],
            $totalConverted >= 10  => ['tier' => AffiliateTier::Silver,   'rate' => 35.00],
            default                => ['tier' => AffiliateTier::Standard, 'rate' => 30.00],
        };
    }
}
```

### 5.4 `RecordReferralClickAction`

```php
namespace App\Modules\Referral\Actions\Click;

/**
 * يُخزَّن سجل ReferralClick في DB.
 * يُستدعى من TrackReferralCode Middleware.
 *
 * قاعدة: لا يَرمي استثناء أبداً — التتبع لا يكسر تجربة المستخدم.
 * يُسجَّل في Log::warning عند الفشل فقط.
 */
class RecordReferralClickAction
{
    public function execute(ReferralClickDTO $dto): ?ReferralClick
    {
        try {
            return ReferralClick::create([
                'id'            => (string) Str::ulid(),
                'affiliate_id'  => $dto->affiliateId,
                'visitor_token' => $dto->visitorToken,  // ULID من Cookie
                'ip_address'    => $dto->ipAddress,
                'user_agent'    => $dto->userAgent,
                'landing_page'  => $dto->landingPage,
            ]);
        } catch (\Throwable $e) {
            Log::warning('RecordReferralClick failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
```

### 5.5 `CreateReferralCommission` Listener

```php
namespace App\Modules\Referral\Listeners;

use App\Modules\Billing\Events\SubscriptionActivated;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateReferralCommission implements ShouldQueue
{
    public bool   $afterCommit = true;
    public string $queue       = 'referrals';    // Queue مستقلة — راجع §16
    public int    $tries       = 3;
    public int    $backoff     = 60;             // ثانية بين المحاولات

    public function __construct(
        private readonly ReferralService      $referralService,
        private readonly FraudDetectionService $fraudService,
    ) {}

    public function handle(SubscriptionActivated $event): void
    {
        // [GUARD 1] تجديد وليس تفعيلًا أولًا → خروج فوري
        if (! $event->isFirstActivation) {
            return;
        }

        $subscription = $event->subscription;
        $user         = $subscription->user;

        // [GUARD 2] هل المستخدم مُحال أصلًا؟
        if (! $affiliate = $user->referredByAffiliate) {
            return;
        }

        // [GUARD 3] هل سبق تسجيل عمولة لهذا الاشتراك؟ (دفاع ثانٍ فوق DB UNIQUE)
        if (ReferralCommission::where('subscription_id', $subscription->id)->exists()) {
            return;
        }

        // [FRAUD CHECK] كشف الحالات المشبوهة
        $fraudResult = $this->fraudService->detectSuspiciousConversions($affiliate, $user);

        $rate       = $this->referralService->resolveCommissionRate($affiliate);
        $amount     = $this->referralService->calculateCommission(
            $event->subscription->amount_usd ?? 0,
            $rate
        );

        app(CreateReferralCommissionAction::class)->execute(
            new CreateCommissionDTO(
                affiliateId:         $affiliate->id,
                subscriptionId:      $subscription->id,
                referredUserId:      $user->id,
                amount:              $amount,
                rate:                $rate,
                triggerSource:       $event->triggerSource,
                subscriptionAmount:  $subscription->amount_usd ?? 0,
                subscriptionPlan:    $subscription->plan->value,
                subscriptionCycle:   $event->cycle,
                fraudFlagged:        $fraudResult->isFlagged,
            )
        );
    }
}
```

---

## 6. هيكل الملفات — Laravel File Structure

```
app/
├── Modules/
│   ├── Billing/
│   │   ├── Events/
│   │   │   ├── SubscriptionActivated.php      ← جديد (v2.1: isFirstActivation + triggerSource)
│   │   │   ├── SubscriptionRenewed.php        ← موجود
│   │   │   ├── SubscriptionRenewalDue.php     ← موجود
│   │   │   └── SubscriptionRenewalFailed.php  ← موجود
│   │   └── Services/
│   │       └── SubscriptionService.php        ← تعديل: event(new SubscriptionActivated(..., isFirstActivation: true))
│   │
│   └── Referral/
│       ├── Actions/
│       │   ├── Affiliate/
│       │   │   ├── CreateAffiliateAction.php
│       │   │   ├── ApproveAffiliateAction.php
│       │   │   └── SuspendAffiliateAction.php
│       │   ├── Click/
│       │   │   └── RecordReferralClickAction.php
│       │   ├── Commission/
│       │   │   ├── CreateReferralCommissionAction.php
│       │   │   ├── ApproveCommissionAction.php
│       │   │   ├── RejectCommissionAction.php
│       │   │   └── UpgradeAffiliateTierAction.php
│       │   └── Payout/
│       │       ├── RequestPayoutAction.php
│       │       └── ProcessPayoutAction.php
│       │
│       ├── Commands/
│       │   └── ReconcileReferralAggregatesCommand.php   ← جديد (§15)
│       │
│       ├── DTOs/
│       │   ├── CreateAffiliateDTO.php     -- بيانات نقل فقط، لا سلوك
│       │   ├── ReferralClickDTO.php
│       │   └── CreateCommissionDTO.php
│       │
│       ├── ValueObjects/                  ← جديد (v2.2) — DDD Value Objects
│       │   └── FraudResult.php            -- Immutable + سلوك clean()/flagged()
│       │
│       ├── Enums/
│       │   ├── AffiliateStatus.php     -- 'pending' | 'active' | 'suspended'
│       │   ├── AffiliateTier.php       -- 'standard' | 'silver' | 'gold' | 'platinum'
│       │   ├── CommissionStatus.php    -- 'pending' | 'approved' | 'paid' | 'rejected' | 'cancelled'
│       │   ├── PayoutMethod.php        -- 'bank' | 'whatsapp' | 'credit'
│       │   └── PayoutStatus.php        -- 'requested' | 'processing' | 'paid' | 'rejected'
│       │
│       ├── Listeners/
│       │   └── CreateReferralCommission.php   ← ShouldQueue + afterCommit + ->onQueue('referrals')
│       │
│       └── Services/
│           ├── ReferralService.php
│           └── FraudDetectionService.php      ← جديد (§17)
│
├── Http/
│   ├── Controllers/
│   │   └── Affiliates/
│   │       ├── AffiliateController.php
│   │       └── PayoutController.php
│   └── Middleware/
│       └── TrackReferralCode.php
│
├── Models/
│   ├── Affiliate.php             ← HasUlids, status/tier/payout_method كـ Backed Enums
│   ├── ReferralClick.php         ← HasUlids
│   ├── ReferralCommission.php    ← HasUlids, status/trigger_source/cycle كـ Backed Enums
│   └── ReferralPayout.php        ← HasUlids, method/status كـ Backed Enums
│
├── Filament/
│   └── Resources/
│       ├── AffiliateResource/
│       │   └── Pages/ {List, Create, Edit, View}
│       ├── ReferralCommissionResource/
│       │   └── Pages/ {List, View}
│       └── ReferralPayoutResource/
│           └── Pages/ {List, Process}
│
database/
└── migrations/
    ├── 2026_07_01_000001_create_affiliates_table.php
    ├── 2026_07_01_000002_create_referral_clicks_table.php
    ├── 2026_07_01_000003_add_referral_columns_to_users_table.php
    ├── 2026_07_01_000004_create_referral_commissions_table.php
    └── 2026_07_01_000005_create_referral_payouts_table.php

routes/
└── web.php
    Route::get('/ref/{identifier}', [AffiliateController::class, 'track'])->name('referral.track');
    Route::middleware(['auth'])->prefix('affiliates')->group(function () {
        Route::get('/join',             [AffiliateController::class, 'join'])->name('affiliates.join');
        Route::post('/join',            [AffiliateController::class, 'store'])->name('affiliates.store');
        Route::get('/dashboard',        [AffiliateController::class, 'dashboard'])->name('affiliates.dashboard');
        Route::get('/commissions',      [AffiliateController::class, 'commissions'])->name('affiliates.commissions');
        Route::get('/payouts',          [PayoutController::class, 'index'])->name('affiliates.payouts');
        Route::post('/payouts/request', [PayoutController::class, 'request'])->name('affiliates.payouts.request');
    });
```

---

## 7. مستويات العمولة — Commission Tiers

### جدول Tiers

| المستوى | Tier Enum (PHP) | DB Value | شرط (total_converted) | نسبة العمولة |
|---------|----------------|----------|----------------------|--------------|
| قياسي   | `AffiliateTier::Standard` | `'standard'` | 0 – 9 | **30%** |
| فضي     | `AffiliateTier::Silver`   | `'silver'`   | 10 – 29 | **35%** |
| ذهبي    | `AffiliateTier::Gold`     | `'gold'`     | 30 – 99 | **40%** |
| بلاتيني | `AffiliateTier::Platinum` | `'platinum'` | 100+ | **45%** |

> الحد الأقصى في البرنامج القياسي: **45%** — لا يُتجاوز إلا باتفاقية خاصة مُوثَّقة.

### قواعد الـ Tier
- الترقية **تلقائية** فور بلوغ عتبة `total_converted` الجديدة
- الترقية **لا تُلغى** حتى لو انخفض `total_converted` (إلغاء الاشتراكات لا يُرجع الـ Tier)
- `commission_rate` يُحدَّث في `affiliates` عند كل ترقية
- العمولات السابقة لا تُعاد حسابها — النسبة المطبَّقة هي تلك في `referral_commissions.rate`

### جدول العمولات بالأرقام

| الخطة    | الدورة | السعر | 30% (Standard) | 35% (Silver) | 40% (Gold) | 45% (Platinum) |
|----------|--------|-------|----------------|--------------|------------|----------------|
| Pro      | شهري  | $17   | $5.10          | $5.95        | $6.80      | $7.65          |
| Pro      | سنوي  | $127  | $38.10         | $44.45       | $50.80     | $57.15         |
| Business | شهري  | $45   | $13.50         | $15.75       | $18.00     | $20.25         |
| Business | سنوي  | $337  | $101.10        | $117.95      | $134.80    | $151.65        |

---

## 8. التكامل مع الأنظمة الحالية

### 8.1 Billing Module — `SubscriptionActivated` (v2.1)

**الحدث الجديد:**
```php
// app/Modules/Billing/Events/SubscriptionActivated.php

namespace App\Modules\Billing\Events;

use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * يُطلَق بعد تفعيل اشتراك.
 *
 * isFirstActivation:
 *   true  → تفعيل أول (يُنشئ عمولة إحالة)
 *   false → تجديد (CreateReferralCommission يتجاهله بالكامل)
 *
 * المستمعون:
 *  - CreateReferralCommission (Referral) — afterCommit, queue: 'referrals'
 *  - [مستقبلاً] RecordRevenueTransaction
 */
class SubscriptionActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly bool         $isFirstActivation = true,
        public readonly string       $triggerSource     = 'togo_callback',
        public readonly string       $cycle             = 'monthly',
    ) {}
}
```

**التعديل على `SubscriptionService::activatePlan()`:**
```php
// سطر واحد يُضاف بعد Subscription::updateOrCreate():

event(new SubscriptionActivated(
    subscription:      $subscription,
    isFirstActivation: true,
    triggerSource:     config('billing.provider', 'manual_admin'),
    cycle:             $cycle,
));
```

---

### 8.2 Togo Payment Provider

لا تعديل على `TogoPaymentService`. التكامل عبر `SubscriptionService::activatePlan()`:

```
Togo Callback → BillingController::togoCallback()
    └─► SubscriptionService::activatePlan(..., cycle: $cycle)
            └─► event(new SubscriptionActivated(..., isFirstActivation: true, triggerSource: 'togo_callback'))
                    └─► Queue 'referrals': CreateReferralCommission   ✓
```

---

### 8.3 Manual Billing Activation (Filament Admin)

```
Admin → Filament SubscriptionResource → activatePlan() / reactivatePlan()
    └─► SubscriptionService::activatePlan(..., cycle: $cycle)
            └─► event(new SubscriptionActivated(..., isFirstActivation: true, triggerSource: 'manual_admin'))
                    └─► Queue 'referrals': CreateReferralCommission   ✓
```

> `$subscription->payment_provider` يُميّز مصدر التفعيل: `'togo'` مقابل `'manual'`.

---

### 8.4 Transactions Module — تسجيل العمولات كمصروفات

عند الموافقة على عمولة (`status → 'approved'`)، يُسجَّل مصروف في `transactions`:

```php
// ApproveCommissionAction::execute()
$transactionData = new TransactionData(
    type:             TransactionType::Expense,
    amount:           $commission->amount,
    currency:         'USD',
    description:      "عمولة إحالة — {$affiliate->name} — {$commission->subscription_plan}",
    transaction_date: now(),
    category_id:      Category::referralCommission()->id,
    reference:        "REFERRAL-{$commission->id}",
    notes:            "مسوّق: {$affiliate->id} | مستخدم: {$commission->referred_user_id}",
);

app(CreateTransactionAction::class)->execute($transactionData);
```

---

### 8.5 CRM Module — `ClientSource::referral`

`ClientSource::Referral` موجود مسبقًا في `app/Modules/CRM/Enums/ClientSource.php` — لا تعديل على الـ Enum:

```php
// CreateClientAction — إضافة منطق اختياري:
if (auth()->user()?->referred_by_affiliate_id) {
    $dto->source = ClientSource::Referral;
}
```

---

### 8.6 Notifications Module

```
للمسوّق:
  AffiliateApprovedNotification      → بريد + إشعار داخلي
  NewCommissionEarnedNotification    → بريد
  TierUpgradedNotification           → بريد + إشعار داخلي
  PayoutProcessedNotification        → بريد

للأدمن (Filament):
  NewAffiliateJoinedNotification     → Filament notification
  PayoutRequestedNotification        → Filament notification
  FraudFlaggedNotification           → Filament notification (§17)
```

---

## 9. لوحة تحكم المسوّق

### الصفحات

| المسار | الغرض |
|--------|-------|
| `/affiliates/join` | نموذج الانضمام |
| `/affiliates/dashboard` | لوحة التحكم الرئيسية |
| `/affiliates/commissions` | قائمة العمولات التفصيلية |
| `/affiliates/payouts` | سجل الصرف + طلب جديد |

### تصميم `/affiliates/dashboard`

```
┌───────────────────────────────────────────────────────────────────┐
│  برنامج الإحالات · مرحباً {name} · Tier: Silver (35%)             │
├──────────────┬──────────────┬──────────────┬─────────────────────┤
│ إجمالي التسجيل│اشتراكات مدفوعة│إجمالي المكتسب│  الرصيد القابل للصرف│
│      24       │      7        │   $91.20     │       $71.20        │
├──────────────┴──────────────┴──────────────┴─────────────────────┤
│  🔗 رابطك:  darahum.com/ref/01J4XY... [📋 نسخ]                    │
│  🏷️ كودك:   AHMED2026          [📋 نسخ]                            │
├───────────────────────────────────────────────────────────────────┤
│  نسبتك الحالية: 35% ← 3 اشتراكات لـ Gold (40%)                   │
│  ██████████████░░░░░ 7 من 10                                      │
├───────────────────────────────────────────────────────────────────┤
│  آخر العمولات                                                     │
│  ┌─────────┬──────────────┬────────────┬──────────┬────────────┐  │
│  │ م.أ.   │ 20/6/2026    │ Pro سنوي   │ $44.45   │ ✅ معتمدة │  │
│  │ خ.م.   │ 22/6/2026    │ Business   │ $13.50   │ ⏳ معلّقة │  │
│  │ س.ف.   │ 24/6/2026    │ —          │ —        │ 🆓 مجاني  │  │
│  └─────────┴──────────────┴────────────┴──────────┴────────────┘  │
├───────────────────────────────────────────────────────────────────┤
│  [💳 طلب صرف]  (الحد الأدنى $20 · رصيدك: $71.20)                │
└───────────────────────────────────────────────────────────────────┘
```

### قواعد العرض
- أسماء المُحالين: `م.أ.` فقط (الحرف الأول من الاسم الأول والأخير)
- لا بريد إلكتروني ولا أي بيانات شخصية
- `balance = total_earned - total_paid` — تُحسب لحظيًا

---

## 10. قواعد البرنامج وحدوده

| القاعدة | التفصيل |
|---------|---------|
| **ULID أساسي** | `/ref/{ulid}` هو المسار القياسي. `display_code` يُوجَّه له بـ redirect |
| **حساب واحد لكل مستخدم** | `UNIQUE INDEX uidx_affiliates_user (user_id)` — مستخدم دراهم الواحد لا يمتلك أكثر من حساب إحالة |
| **Self-referral محظور** | يُكشف عبر `FraudDetectionService` → يُسجَّل + يُرفض |
| **Attribution دائمة** | أول مسوّق يُحيل المستخدم يكسب العمولة — لا يُغيَّر |
| **عمولة واحدة لكل اشتراك** | `UNIQUE INDEX` على `referral_commissions.subscription_id` |
| **لا عمولة للتجديد** | `SubscriptionActivated.isFirstActivation = false` → Listener يُرجع فوراً |
| **Cookie 60 يومًا** | `referral_visitor_token` ULID Cookie — تنتهي بعد 60 يومًا |
| **7 أيام pending قبل الاعتماد** | العمولات الجديدة تبقى `pending` 7 أيام (حماية من الاحتيال) |
| **حد الصرف الأدنى** | $20 — يُرفض بالكود قبل وصوله للـ DB |
| **موافقة يدوية على الانضمام** | لا قبول تلقائي في Phase 1 |
| **الحد الأقصى للعمولة** | 45% في البرنامج القياسي |
| **No MySQL ENUMs** | جميع الأعمدة VARCHAR + CHECK + PHP Backed Enum |

---

## 11. خارطة التنفيذ — Roadmap

### Phase 1 — MVP

| المهمة | الأولوية |
|--------|---------|
| Migrations (5 جداول — VARCHAR بدون ENUM) | 🔴 حرجة |
| Models (HasUlids + Backed Enums في Casts) | 🔴 حرجة |
| `SubscriptionActivated` Event (v2.1 مع isFirstActivation) | 🔴 حرجة |
| `CreateReferralCommission` Listener (afterCommit + queue: 'referrals') | 🔴 حرجة |
| `TrackReferralCode` Middleware (ULID Cookie) | 🔴 حرجة |
| `FraudDetectionService` (أساسي: self-referral + click spam) | 🔴 حرجة |
| Attribution في RegisterUserController | 🔴 حرجة |
| `ReferralService` كامل | 🔴 حرجة |
| `ReconcileReferralAggregatesCommand` + Scheduler | 🟠 عالية |
| صفحة `/affiliates/join` + `/affiliates/dashboard` | 🟠 عالية |
| طلب صرف عبر واتساب | 🟠 عالية |
| Filament Resources (Affiliates + Commissions + Payouts) | 🟠 عالية |
| إعداد Queue `referrals` في Supervisor | 🟠 عالية |
| Notifications (Approved + Commission + Payout) | 🟡 متوسطة |
| `FraudFlaggedNotification` للأدمن | 🟡 متوسطة |

### Phase 2 — تحسينات

| المهمة | الملاحظة |
|--------|---------|
| Automated Payouts | ربط مع Togo لدفع العمولات مباشرة |
| Coupon Codes | كوبونات خصم للمستخدمين المُحالين |
| Ambassador Program | شركاء رسميون (50%+ + شارة الشريك) |
| Leaderboards | أفضل 10 مسوّقين |
| Recurring Commissions | عمولة صغيرة على التجديدات (10%) — تحتاج مراجعة |
| Analytics Dashboard | مسار التحويل: Click → Register → Pay |
| `FraudDetectionService` متقدم | TOR/VPN detection + ML patterns |
| WhatsApp Notifications | إشعار فوري للمسوّق عند العمولة |

---

## 12. خطة الـ Migration

### ترتيب التنفيذ (FK order)

```
1. create_affiliates_table
       ↓ (FK: affiliates.user_id → users.id)
2. create_referral_clicks_table
       ↓ (FK: referral_clicks.affiliate_id → affiliates.id)
3. add_referral_columns_to_users_table
       ↓ (FK: users.referred_by_affiliate_id → affiliates.id)
       ↓ (FK: users.referral_click_id → referral_clicks.id)
4. create_referral_commissions_table
       ↓ (FK: commissions.affiliate_id → affiliates.id)
       ↓ (FK: commissions.subscription_id → subscriptions.id)
       ↓ (FK: commissions.referred_user_id → users.id)
5. create_referral_payouts_table
       ↓ (FK: payouts.affiliate_id → affiliates.id)
```

### قيود وملاحظات
- جميع الأعمدة `status/tier/method` من نوع `VARCHAR` — لا `ENUM` — صديقة لـ Zero-downtime
- `referral_commissions.subscription_id` → `ON DELETE RESTRICT` — لا يُحذف subscription بعمولة
- `ADD COLUMN` على `users` فقط — لا تعديل على أعمدة حالية

---

## 13. QA Checklist

### وظيفي (Functional)

- [ ] زيارة `/ref/{ulid}` → تُنشئ `referral_clicks` + Cookie `referral_visitor_token` (ULID، 60 يومًا)
- [ ] زيارة `/ref/{display_code}` → redirect + تُنشئ click بنفس المسوّق
- [ ] زيارة ثانية بنفس المتصفح → نفس `visitor_token` من Cookie
- [ ] Self-referral → لا attribution، يُسجَّل في Log
- [ ] تسجيل بدون Cookie → `referred_by_affiliate_id = null`
- [ ] مستخدم يحاول إنشاء حسابَين كمسوّق → `UNIQUE INDEX uidx_affiliates_user` يمنع الثاني
- [ ] اشتراك Pro شهري مُحال → `referral_commissions.amount = 5.10` (30%)
- [ ] اشتراك Business سنوي مُحال → `amount = 101.10`
- [ ] تجديد (isFirstActivation=false) → لا عمولة جديدة
- [ ] عمولة جديدة → `status = 'pending'` لمدة 7 أيام
- [ ] الاشتراك العاشر → `affiliates.tier = 'silver'`, `commission_rate = 35.00`
- [ ] طلب صرف بـ $15 → مرفوض
- [ ] طلب صرف بـ $25 → `referral_payouts` بـ `status = 'requested'`
- [ ] تعليق مسوّق → عمولاته `pending` تصبح `cancelled`
- [ ] `referral:reconcile` → يُصحَّح total_referrals/converted/earned/paid

### تقني (Technical)

- [ ] Listener يعمل على Queue `referrals` (وليس `default`)
- [ ] `afterCommit = true` → subscription موجود عند تشغيل Listener
- [ ] فشل Queue → retry 3 مرات (backoff 60s) → لا تُفقد العمولة
- [ ] `TrackReferralCode` لا يكسر أي صفحة (try/catch)
- [ ] `RecordReferralClickAction` صامت عند الفشل
- [ ] جميع Models: `HasUlids` + primary key `CHAR(26)`
- [ ] `CHECK constraints` تمنع قيمًا غير صالحة على DB level
- [ ] `UNIQUE(subscription_id)` مُطبَّق في DB

### أمني (Security)

- [ ] ULID لا يمكن تخمينه
- [ ] Cookie `referral_visitor_token` تُرسَل بـ `Secure=true` (HTTPS فقط)
- [ ] Cookie `referral_visitor_token` تحمل `HttpOnly=true` (لا `document.cookie`)
- [ ] Cookie `referral_visitor_token` تحمل `SameSite=lax`
- [ ] Cookie تُضبط على `session.domain` (يشمل الـ subdomains)
- [ ] `visitor_token` Cookie مستقلة عن IP وUser Agent
- [ ] لا بريد/بيانات شخصية في dashboard المسوّق
- [ ] 20 click/IP/day → رفض الزائد
- [ ] `display_code` → alphanumeric فقط، max 50 حرفًا
- [ ] الأدمن فقط يُعدِّل `commission_rate`

### توافق قاعدة البيانات (DB Compatibility)

- [ ] MySQL ≥ 8.0.16 أو MariaDB ≥ 10.4 مُثبَّت في البيئة
- [ ] CHECK constraints تعمل فعلًا (اختبار إدخال قيمة خارج القائمة → يُرفض)
- [ ] `referral:reconcile --dry-run` يعمل بدون أخطاء بعد Migration

---

## 14. ملاحظات التوسّع المستقبلي

### Recurring Commissions
يكفي لاحقًا:
1. إضافة مستمع `CreateRenewalCommission` على `SubscriptionRenewed` الموجود
2. إضافة `UNIQUE NULL` على `subscription_id` بدلًا من `UNIQUE NOT NULL`

### Multi-currency
`currency CHAR(3)` جاهز في `referral_commissions`. عند الحاجة: يُضاف `exchange_rate DECIMAL(10,6)` فقط.

### Sub-affiliates
إذا أُضيف: `referred_by_affiliate_id` على `affiliates` نفسه + منطق العمولة المتدرجة في `CreateReferralCommissionAction`.

### API للشركاء
`GET /api/v1/affiliates/stats` محمي بـ Sanctum — للشركاء الكبار.

---

## 15. Referral Aggregates Reconciliation

### المشكلة
الأعمدة `total_referrals`, `total_converted`, `total_earned`, `total_paid` في جدول `affiliates` هي **Denormalized Aggregates** — تُحدَّث تدريجيًا عبر Actions ولا تُحسب في كل مرة من الجداول المصدرية. مع الوقت، قد تتراكم تناقضات طفيفة بسبب:
- Queue فاشلة أُعيدت جزئيًا
- تعليق مسوّق وإلغاء عمولاته يدويًا
- أخطاء غير متوقعة

### الحل: أمر مطابقة يومي

```php
// app/Modules/Referral/Commands/ReconcileReferralAggregatesCommand.php

namespace App\Modules\Referral\Commands;

use Illuminate\Console\Command;

/**
 * php artisan referral:reconcile
 *
 * يعيد حساب جميع Aggregates في affiliates من الجداول المصدرية.
 * يُسجَّل أي فارق في Log مع تفاصيله.
 * آمن للتشغيل في Production — UPDATE فقط، لا DELETE.
 */
class ReconcileReferralAggregatesCommand extends Command
{
    protected $signature   = 'referral:reconcile {--dry-run : عرض الفوارق بدون تطبيق}';
    protected $description = 'Reconcile referral aggregate counters from source tables';

    public function handle(): int
    {
        $affiliates = Affiliate::where('status', 'active')->cursor();

        foreach ($affiliates as $affiliate) {
            $computed = [
                'total_referrals' => User::where('referred_by_affiliate_id', $affiliate->id)->count(),

                // total_converted يشمل 'pending' لضمان الترقية الفورية للـ Tier
                // قبل انتهاء نافذة الـ 7 أيام للاحتيال.
                // راجع: "Tier Progression Policy" أدناه.
                'total_converted' => ReferralCommission::where('affiliate_id', $affiliate->id)
                    ->whereIn('status', ['pending', 'approved', 'paid'])
                    ->count(),

                // total_earned يشمل الحالات المعتمدة فقط (لا pending)
                // لأن العمولات المعلّقة قد تُرفض أو تُلغى.
                'total_earned'    => ReferralCommission::where('affiliate_id', $affiliate->id)
                    ->whereIn('status', ['approved', 'paid'])
                    ->sum('amount'),

                'total_paid'      => ReferralPayout::where('affiliate_id', $affiliate->id)
                    ->where('status', 'paid')
                    ->sum('amount'),
            ];

            $diffs = array_filter(
                $computed,
                fn($val, $key) => (float) $affiliate->{$key} !== (float) $val,
                ARRAY_FILTER_USE_BOTH
            );

            if (! empty($diffs)) {
                Log::warning('referral:reconcile drift', [
                    'affiliate_id' => $affiliate->id,
                    'diffs'        => $diffs,
                ]);

                if (! $this->option('dry-run')) {
                    $affiliate->update($computed);
                }
            }
        }

        $this->info('Reconciliation complete.');
        return self::SUCCESS;
    }
}
```

### سياسة الترقية الفورية — Tier Progression Policy

> **المشكلة:** العمولات الجديدة تبقى بحالة `pending` لمدة 7 أيام (نافذة مكافحة الاحتيال). إذا احتسبنا `total_converted` من `['approved', 'paid']` فقط، سيتأخر ترقية المسوّق للـ Tier الأعلى بأسبوع كامل بعد تحقيقه العتبة.
>
> **الحل المعتمد:** `total_converted` يُحسب من `['pending', 'approved', 'paid']`.
>
> **المبرر:** الاشتراك المدفوع حقيقي وموثَّق. احتمال رفض العمولة لاحتيال موجود لكن نادر، ولا يستحق تأخير تجربة المسوّق. إذا رُفضت عمولة لاحقًا يُنقَّح `total_converted` في الـ Reconciliation التالي.
>
> **مثال توضيحي:**
> ```
> اليوم: المسوّق يُحقق الاشتراك العاشر
> → total_converted = 10 (يشمل الاشتراكات pending)
> → UpgradeAffiliateTierAction: Standard → Silver (35%) فوراً ✓
>
> بعد 7 أيام:
> → إذا اعتُمدت العمولات: total_earned يُحدَّث → لا تغيير في Tier
> → إذا رُفضت عمولة واحدة: total_converted = 9 في Reconciliation → يعود Standard
>   (نادر، ويُعالَج في reconcile:03:30)
> ```
>
> | المقياس | يشمل 'pending' | السبب |
> |---------|---------------|-------|
> | `total_converted` | ✅ نعم | الترقية الفورية للـ Tier |
> | `total_earned` | ❌ لا | المبلغ الفعلي المستحق فقط |

### جدولة التشغيل

```php
// app/Console/Kernel.php — أو bootstrap/schedule.php في Laravel 11+

Schedule::command('referral:reconcile')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        Log::error('referral:reconcile scheduled run failed');
    });
```

---

## 16. Queue Architecture

### هيكل الـ Queues في دراهم

| Queue | الغرض | Priority |
|-------|-------|---------|
| `default` | أعمال متنوعة لا تنتمي لأي سياق خاص | Low |
| `billing` | عمليات Togo، التحقق من المدفوعات | High |
| `notifications` | إرسال الإيميلات والإشعارات | Medium |
| `crm` | CRM automations، Health score، Import/Export | Medium |
| `referrals` | إنشاء العمولات، Fraud checks، Tier upgrades | Medium |
| `exports` | PDF، Excel، تقارير | Low |

> **القاعدة الأساسية:** `Referral processing must never block billing operations.`
>
> العمليات المالية الحرجة (Togo callbacks، تفعيل الاشتراكات) تعمل على Queue `billing` المنفصلة. عمليات الإحالة تعمل على `referrals` — حتى لو تأخّرت أو فشلت لا تؤثر على تدفق الدفع.

### إعداد Supervisor

```ini
# /etc/supervisor/conf.d/darahum-workers.conf

[program:darahum-billing]
command=php /var/www/darahum/artisan queue:work --queue=billing --tries=5 --timeout=60
numprocs=2
autostart=true
autorestart=true

[program:darahum-referrals]
command=php /var/www/darahum/artisan queue:work --queue=referrals --tries=3 --timeout=30
numprocs=1
autostart=true
autorestart=true

[program:darahum-notifications]
command=php /var/www/darahum/artisan queue:work --queue=notifications --tries=3 --timeout=30
numprocs=1
autostart=true
autorestart=true

[program:darahum-default]
command=php /var/www/darahum/artisan queue:work --queue=crm,exports,default --tries=3 --timeout=60
numprocs=1
autostart=true
autorestart=true
```

### تعريف Queue في Listener

```php
class CreateReferralCommission implements ShouldQueue
{
    public bool   $afterCommit = true;
    public string $queue       = 'referrals';   // ← صريح وثابت
    public int    $tries       = 3;
    public int    $backoff     = 60;
}
```

---

## 17. Fraud Prevention

### قواعد مكافحة الاحتيال

| # | القاعدة | الإجراء |
|---|---------|---------|
| 1 | أكثر من 20 click من نفس IP في يوم واحد | رفض الـ clicks الزائدة صامتًا + تسجيل في Log |
| 2 | تسجيلات متعددة من نفس الجهاز (visitor_token) | يُحوَّل للمراجعة اليدوية (`fraud_flagged = 1`) |
| 3 | Self-referral | يُسجَّل + يُرفض + يُبلَّغ الأدمن |
| 4 | TOR/VPN traffic | يُفعَّل في Phase 2 — يتطلب خدمة خارجية |
| 5 | العمولة تبقى `pending` 7 أيام | الموافقة التلقائية بعد 7 أيام (أو يدوية فوراً) |
| 6 | تطابق طريقة الصرف بين المسوّق والمُحال | `fraud_flagged = 1` + تنبيه الأدمن |
| 7 | ارتفاع غير طبيعي في التحويلات | Notification للأدمن إذا تجاوز 5 conversions في يوم |

### `FraudDetectionService`

```php
// app/Modules/Referral/Services/FraudDetectionService.php

namespace App\Modules\Referral\Services;

/**
 * FraudDetectionService — كشف أنماط الاحتيال في برنامج الإحالات
 *
 * مبدأ التصميم: لا يُوقف أي تدفق — يُعيد FraudResult فقط.
 * القرار النهائي (رفض/قبول/تعليق) يعود للـ Action أو الأدمن.
 */
class FraudDetectionService
{
    /**
     * يكشف Self-referral: المسوّق يسجّل من رابطه.
     * يُسجَّل في Log دائمًا حتى لو لم يُطبَّق.
     */
    public function detectSelfReferral(Affiliate $affiliate, User $user): bool
    {
        $isSelf = $affiliate->user_id === $user->id;

        if ($isSelf) {
            Log::warning('Referral: self-referral attempt', [
                'affiliate_id' => $affiliate->id,
                'user_id'      => $user->id,
            ]);
        }

        return $isSelf;
    }

    /**
     * يكشف Click Spam: أكثر من 20 click من نفس IP في يوم واحد.
     * لا يُوقف الطلب — يُرجع true إذا تجاوز الحد.
     */
    public function detectClickSpam(string $ipAddress): bool
    {
        $count = ReferralClick::where('ip_address', $ipAddress)
            ->whereDate('created_at', today())
            ->count();

        return $count >= 20;
    }

    /**
     * يكشف حسابات مكرّرة: نفس visitor_token يُستخدم لتسجيلات متعددة.
     */
    public function detectDuplicateAccounts(string $visitorToken): bool
    {
        return User::whereHas('referralClick', fn($q) =>
            $q->where('visitor_token', $visitorToken)
        )->count() > 1;
    }

    /**
     * يكشف أنماط مشبوهة في التحويل:
     * - تطابق payout_method بين المسوّق والمُحال
     * - ارتفاع غير طبيعي (5+ conversions في يوم)
     *
     * يُرجع FraudResult::flagged() أو FraudResult::clean()
     */
    public function detectSuspiciousConversions(
        Affiliate $affiliate,
        User $user
    ): FraudResult {
        $reasons = [];

        // فحص تطابق طريقة الصرف
        if ($affiliate->payout_method && $affiliate->payout_details) {
            // منطق المقارنة يعتمد على payout_details JSON
            // يُفعَّل عند توفر بيانات كافية
        }

        // فحص الارتفاع الغير طبيعي
        $todayConversions = ReferralCommission::where('affiliate_id', $affiliate->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayConversions >= 5) {
            $reasons[] = "high_conversion_rate: {$todayConversions} conversions today";

            Notification::route('mail', config('mail.admin_address'))
                ->notify(new FraudFlaggedNotification($affiliate, $reasons));
        }

        return empty($reasons)
            ? FraudResult::clean()
            : FraudResult::flagged($reasons);
    }
}
```

### `FraudResult` Value Object

```php
// app/Modules/Referral/ValueObjects/FraudResult.php
// ⚠️ ValueObjects/ وليس DTOs/ — راجع الملاحظة المعمارية أدناه

final class FraudResult
{
    private function __construct(
        public readonly bool  $isFlagged,
        public readonly array $reasons = [],
    ) {}

    public static function clean(): self
    {
        return new self(isFlagged: false);
    }

    public static function flagged(array $reasons): self
    {
        return new self(isFlagged: true, reasons: $reasons);
    }
}
```

> ### ملاحظة معمارية: FraudResult هو Value Object وليس DTO
>
> | المعيار | DTO | Value Object (FraudResult) |
> |---------|-----|--------------------------|
> | الغرض | نقل بيانات بين الطبقات | تمثيل مفهوم نطاقي (domain concept) |
> | السلوك | لا سلوك — بيانات فقط | يحتوي سلوكًا: `clean()` و`flagged()` |
> | Immutability | اختياري | إلزامي (`final + readonly`) |
> | الموقع | `DTOs/` | `ValueObjects/` |
> | مثال في دراهم | `CreateCommissionDTO`, `ReferralClickDTO` | `FraudResult` |
>
> `FraudResult` لا يُمرَّر بين طبقات كـ data carrier — بل يُعبَّر عن نتيجة قرار (clean/flagged) مع سلوك مدمج. هذا تعريف Value Object وفق DDD المستخدم في دراهم.

### تكامل Fraud مع Pipeline العمولة

```
SubscriptionActivated Event
    └─► CreateReferralCommission Listener
            └─► FraudDetectionService::detectSuspiciousConversions()
                    ├── FraudResult::clean()    → commission.fraud_flagged = 0, status = 'pending' (7 أيام)
                    └── FraudResult::flagged()  → commission.fraud_flagged = 1, status = 'pending' (يدوي)
                                                   + FraudFlaggedNotification → Filament Admin
```

---

## 18. Database Compatibility Requirements

### الإصدارات المدعومة

| قاعدة البيانات | الحد الأدنى | السبب |
|---------------|------------|-------|
| **MySQL** | **8.0.16+** | أول إصدار يُطبَّق فيه CHECK constraints فعليًا |
| **MariaDB** | **10.4+** | CHECK constraints مدعومة منذ 10.2، لكن 10.4+ مستقرة |

> ⛔ **MySQL 5.7 غير مدعوم في موديول الإحالات.**
>
> في MySQL 5.7، يقبل المحرك صياغة CHECK constraints لكنه يتجاهلها تمامًا — القيم تُخزَّن بدون أي تحقق. هذا يكسر ضمانات سلامة البيانات التي يعتمد عليها موديول الإحالات.

### مثال على السلوك المتوقع

```sql
-- على MySQL 8.0.16+ أو MariaDB 10.4+
INSERT INTO affiliates (id, name, email, status)
VALUES ('01J4XY...', 'Ahmed', 'a@test.com', 'invalid_status');
-- ❌ ERROR 3819 (HY000): Check constraint 'affiliates_chk_1' is violated.

-- على MySQL 5.7
INSERT INTO affiliates (id, name, email, status)
VALUES ('01J4XY...', 'Ahmed', 'a@test.com', 'invalid_status');
-- ⚠️ Query OK — بيانات فاسدة مخزَّنة بصمت
```

### التحقق من الإصدار قبل Migration

```bash
# تحقق من إصدار MySQL/MariaDB
mysql --version
# أو داخل MySQL shell:
SELECT VERSION();

# يجب أن يكون:
# MySQL:   8.0.16 أو أحدث
# MariaDB: 10.4.0 أو أحدث
```

### العلاقة بـ No MySQL ENUMs

قرار استخدام `VARCHAR + CHECK` (القسم §3) يستلزم هذه المتطلبات مباشرةً. إذا كانت بيئة الـ Production تشغّل MySQL 5.7، فإما الترقية لـ MySQL 8+ وإما استخدام PHP Enum validation فقط مع قبول غياب الحماية على مستوى DB — وهو غير مقبول في سياق هذا الموديول.

### خطوة التحقق في CI/CD

```php
// يُضاف في TestCase::setUp() أو في migration test:
$version = DB::selectOne('SELECT VERSION() as v')->v;
$isSupported = version_compare($version, '8.0.16', '>=')
    || (str_contains($version, 'MariaDB') && version_compare(
        preg_replace('/.*MariaDB-/', '', $version), '10.4', '>='
    ));

$this->assertTrue($isSupported, "DB version {$version} does not support CHECK constraints.");
```

---

## 19. Implementation Approval

```
┌─────────────────────────────────────────────────────────┐
│              REFERRAL PROGRAM — APPROVAL RECORD         │
├─────────────────────────────────────────────────────────┤
│  Document:   docs/REFERRAL-PROGRAM.md                   │
│  Version:    2.2                                        │
│  Status:     ✅ Approved For Implementation             │
│  Date:       27 يونيو 2026                              │
├─────────────────────────────────────────────────────────┤
│  Blocking Issues:  None                                 │
└─────────────────────────────────────────────────────────┘
```

### متطلبات قبل بدء التطوير

هذه المهام يجب إنجازها قبل كتابة أي سطر كود من الموديول:

- [ ] التحقق من إصدار MySQL/MariaDB في بيئة الـ Production (راجع §18)
- [ ] إنشاء الـ Migrations بالترتيب الصحيح (راجع §12)
- [ ] تسجيل Queue `referrals` في Supervisor (راجع §16)
- [ ] إضافة `SubscriptionActivated` Event في Billing Module (راجع §8.1)
- [ ] إضافة `event(new SubscriptionActivated(...))` في `SubscriptionService::activatePlan()`
- [ ] جدولة `referral:reconcile` في `Kernel.php` أو `bootstrap/schedule.php` (راجع §15)
- [ ] تفعيل `FraudFlaggedNotification` في Filament (راجع §17)

### Sprint الأول الموصى به

| الترتيب | المهمة | المخرج |
|---------|--------|-------|
| **1** | Database Layer | 5 migrations + 4 models (HasUlids) |
| **2** | Event Infrastructure | SubscriptionActivated + Listener + Queue |
| **3** | Attribution Flow | Middleware + Cookie + RegisterUserController |
| **4** | Affiliate Dashboard | `/affiliates/join` + `/affiliates/dashboard` |
| **5** | Admin Approval Workflow | Filament: AffiliateResource + CommissionResource |

### ما لا يُبنى في Sprint الأول

- Fraud TOR/VPN detection (Phase 2)
- Automated Payouts (Phase 2)
- Leaderboards / Analytics (Phase 2)
- Recurring Commissions (Phase 2 — تحتاج مراجعة تجارية)

### المرجعية النهائية

هذه الوثيقة (v2.2) هي **المرجع الرسمي الوحيد** لنظام الإحالات في دراهم. أي قرار تقني أو تجاري يتعارض مع ما ورد هنا يستلزم تحديث الوثيقة أولًا وإقراره قبل التطبيق.

أي نسخة سابقة (v1.0, v2.0, v2.1) تُعدَّ ملغاة ولا يُستشهد بها.
