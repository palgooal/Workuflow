# تقرير مراجعة الصفحات — Workuflow / دراهم
**تاريخ:** 2026-06-25 | **نوع:** قراءة فقط — لا تعديلات
**الغرض:** جرد الصفحات الموجودة + تقييم الجودة + خطة تحسين معتمدة قبل التنفيذ

---

## قائمة الصفحات المراجعة

| # | الصفحة | المسار |
|---|--------|--------|
| 1 | Dashboard | `resources/views/dashboard.blade.php` |
| 2 | Layout (app) | `resources/views/layouts/app.blade.php` |
| 3 | Sidebar | `resources/views/layouts/partials/sidebar.blade.php` |
| 4 | Navbar | `resources/views/layouts/partials/navbar.blade.php` |
| 5 | Invoices — List | `resources/views/invoices/index.blade.php` |
| 6 | Invoices — Show | `resources/views/invoices/show.blade.php` |
| 7 | Transactions — List | `resources/views/transactions/index.blade.php` |
| 8 | Reports | `resources/views/reports/index.blade.php` |
| 9 | Clients — List | `resources/views/crm/clients/index.blade.php` |
| 10 | Wallets — List | `resources/views/wallets/index.blade.php` |
| 11 | Quotes — List | `resources/views/quotes/index.blade.php` |
| 12 | Projects — List | `resources/views/projects/index.blade.php` |
| 13 | Billing — Index | `resources/views/billing/index.blade.php` |
| 14 | Billing — Upgrade | `resources/views/billing/upgrade.blade.php` |
| 15 | Settings | `resources/views/settings/index.blade.php` |
| 16 | Auth — Login | `resources/views/auth/login.blade.php` |

**ملاحظة:** الصفحات التالية لم تُراجَع بعد (مؤجلة للجولة الثانية): `projects/show`, `projects/create`, `projects/_card`, `transactions/create`, `clients/show`, `quotes/show`, `wallets/show`, `debts/index`, `budgets/index`, `recurring/index`, `categories/index`, `notifications/index`, `help/index`, `profile/edit`, `marketing/*`, `auth/register`, `auth/forgot-password`.

---

## مشاكل مشتركة عبر جميع الصفحات

قبل مراجعة كل صفحة منفردة، هذه المشاكل تتكرر في أكثر من نصف الصفحات:

### M1 — خصائص RTL الفيزيائية بدلاً من المنطقية
**الوصف:** استخدام `ml-`, `mr-`, `left-`, `right-` عوضاً عن `ms-`, `me-`, `start-`, `end-` في سياقات RTL.
**الخطر:** تنعكس المسافات عند تشغيل LTR أو في أدوات الاختبار.
**أمثلة:** navbar dropdown يستخدم `left-0`، toast في clients يستخدم `left-1/2`، transactions icon margin يستخدم `ml-3`.

### M2 — كلاسات `dark:` ميتة
**الوصف:** `dark:` موجودة في billing/index, billing/upgrade, wallets/index, upgrade-modal، لكن لا يوجد toggle لـ dark mode في الـ UI.
**الخطر:** ضجيج في الكود، وتشويش عند إضافة dark mode لاحقاً (لأن التغطية غير منتظمة).

### M3 — ألوان hardcoded بدلاً من design tokens
**الوصف:** كثير من الصفحات تستخدم `text-slate-500`, `text-slate-900`, `bg-emerald-*`, `teal-*` بدلاً من `text-ink`, `text-muted`, `bg-success-soft`.
**الخطر:** لو تغيّر اللون الأساسي مستقبلاً، سيحتاج البحث في كل ملف بدلاً من تغيير المتغير فقط.

### M4 — أسهم الاتجاه LTR
**الوصف:** روابط "العودة" و"التالي" تستخدم `←` / `→` في صفحات RTL. الصح في العربية: `→` للعودة (عكس اتجاه القراءة).
**أمثلة:** billing/upgrade: `← العودة لصفحة الاشتراك`، dashboard: `←` في روابط التفاصيل.

### M5 — تكرار flash messages
**الوصف:** wallets/index يعيد عرض `session('success')` داخل الصفحة رغم أن layout/app.blade.php يعرضه هو.
**الأثر:** يظهر نفس الإشعار مرتين.

---

## تقرير كل صفحة

---

### 1. Dashboard (`dashboard.blade.php`)
**التقييم:** ⭐⭐⭐⭐ ممتاز — أفضل صفحة في التطبيق

**ما يعمل جيداً:**
- استخدام منتظم لـ design tokens (`dash-card`, `space-y-6`, `text-ink`, `text-muted`, `nums`)
- بنية متقنة: 4-col KPI → 2-col wallets/invoices → Chart.js → 3-col recent data
- Chart.js cash flow موثق بتعليق لماذا يُحمَّل من CDN
- شريط تدرجي في بطاقة الترحيب واضح ومتعمد

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| D1 | `text-[13px]` hardcoded بدلاً من `text-xs` أو custom class | منخفضة |
| D2 | روابط "التفاصيل" تستخدم `←` (LTR arrow) في صفحة RTL | منخفضة |

---

### 2. Layout — App (`layouts/app.blade.php`)
**التقييم:** ⭐⭐⭐⭐ صلب — أساس قوي

**ما يعمل جيداً:**
- `<html lang="ar" dir="rtl">` صحيح
- `min-h-screen flex` مع Alpine sidebar state
- Upgrade modal ووhBoarding modal موصولان عالمياً
- Flash messages للأخطاء وvalidation errors

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| A1 | success flash يظهر فقط على mobile (`sm:hidden`) — لا يظهر على desktop في body | متوسطة |
| A2 | تكرار success flash: layout يعرضه + بعض الصفحات تعرضه مجدداً | متوسطة |

---

### 3. Sidebar (`layouts/partials/sidebar.blade.php`)
**التقييم:** ⭐⭐⭐⭐ جيد جداً

**ما يعمل جيداً:**
- `fixed inset-y-0 right-0` صحيح RTL
- تجميع منطقي: الرئيسية / المالية / الأعمال / التحليل / الدعم
- Wallets مقيّد ببادج "Pro" واضح
- خطة المستخدم في الأسفل مع CTA للترقية

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| S1 | كلاسات `dark:` موجودة بدون dark mode toggle (M2) | منخفضة |
| S2 | لا يوجد label/tooltip للأيقونات على الـ collapsed state | منخفضة |

---

### 4. Navbar (`layouts/partials/navbar.blade.php`)
**التقييم:** ⭐⭐⭐½ جيد مع ملاحظة واحدة مؤثرة

**ما يعمل جيداً:**
- `sticky top-0 z-sticky bg-surface/90 backdrop-blur-md`
- Notification badge مع cap `9+`
- User dropdown مع Avatar ومعلومات المستخدم

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| N1 | Dropdown يستخدم `left-0` (LTR) في صفحة RTL — الصح `start-0` أو `right-0` (M1) | متوسطة |
| N2 | Success flash لا يظهر على desktop في body — يظهر في navbar فقط للـ desktop (A1) | متوسطة |

---

### 5. Invoices — List (`invoices/index.blade.php`)
**التقييم:** ⭐⭐⭐⭐ ممتاز — مثال يُحتذى به

**ما يعمل جيداً:**
- استخدام منتظم للكومبوننتس: `x-stat-grid`, `x-stats-card`, `x-data-table`, `x-table-th`, `x-page-header`, `x-empty-state`
- جدول responsive صحيح (project hidden `md:`, dates hidden `lg:`)
- Breadcrumbs

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| I1 | Flash message مكرر داخل الصفحة رغم أن layout يعالجه (M5) | منخفضة |

---

### 6. Invoices — Show (`invoices/show.blade.php`)
**التقييم:** ⭐⭐½ يحتاج مراجعة — الصفحة ذات الأولوية الأعلى في التحسين

**ما يعمل جيداً:**
- Action bar يستخدم `flex flex-wrap` — مناسب للموبايل

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| IS1 | أزرار الإجراءات تستخدم emoji كـ labels (📧 🖨️ ✏️ ✅ 📤) بدلاً من SVG icons المتسقة | عالية |
| IS2 | `text-left` للتواريخ والمبالغ في صفحة RTL — يعكس المحاذاة | عالية |
| IS3 | `teal-600` في Pay Modal خارج نظام الألوان (النظام يستخدم `accent`/green family) | متوسطة |
| IS4 | ألوان `slate-*` hardcoded بدلاً من design tokens (M3) | متوسطة |
| IS5 | `grid grid-cols-2 gap-8` في قسم العميل/المشروع — لا responsive variant، ضيق على موبايل | عالية |

---

### 7. Transactions — List (`transactions/index.blade.php`)
**التقييم:** ⭐⭐⭐½ جيد مع ملاحظات

**ما يعمل جيداً:**
- منطق ذكي: single-currency → 3 stat cards، multi-currency → جدول موحّد
- تحذير واضح للمستخدم عند عملات متعددة
- `x-filter-bar` و`x-empty-state` components
- Row hover actions مخفية وتظهر عند hover

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| T1 | `text-left` على header المبلغ (السطر 132) في صفحة RTL | متوسطة |
| T2 | `ml-3` على icon (السطر 140) — خاصية فيزيائية بدلاً من `ms-3` (M1) | منخفضة |
| T3 | `text-slate-600` للمشروع والفئة بدلاً من `text-muted` (M3) | منخفضة |
| T4 | `confirm()` browser dialog للحذف — UX قديم ومحدود التخصيص | منخفضة |
| T5 | الفلتر بتاريخ البداية `date_from` فقط — لا يوجد `date_to` في filter-bar (ملاحظة: قد يكون مقصوداً) | منخفضة |

---

### 8. Reports (`reports/index.blade.php`)
**التقييم:** ⭐⭐⭐ متوسط — المنطق صحيح لكن البنية غير متسقة

**ما يعمل جيداً:**
- منطق gating صحيح: export مخفي للـ free users مع popover ترقية أنيق
- فلتر التاريخ مدمج في الـ header
- `x-page-header` غير مستخدم هنا لكن التصميم يعوّض ذلك

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| R1 | Header لا يستخدم `x-page-header` — تنسيق مختلف عن باقي الصفحات | متوسطة |
| R2 | أزرار التصدير في header div مع title بدلاً من `x-page-header actions slot` | متوسطة |
| R3 | ألوان hardcoded: `bg-red-50`, `bg-green-50`, `border-red-200`, `text-red-700` بدلاً من tokens | منخفضة |
| R4 | Upgrade popover يستخدم `right-0` بدلاً من `start-0` — يخالف RTL logical properties | متوسطة |

---

### 9. Clients — List (`crm/clients/index.blade.php`)
**التقييم:** ⭐⭐⭐⭐ متقدم جداً — infinite scroll + live search + bulk actions + import modal

**ما يعمل جيداً:**
- Infinite scroll مع IntersectionObserver
- Live search بـ debounce 350ms بدون page reload
- Bulk actions (archive, tag) مع Alpine UI
- Import modal multi-step مع polling وprogress bar
- Client limit CTA واضح (رغم أنه disabled button لا upgrade card — انظر IS*)

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| CL1 | ألوان `text-slate-*` في كل مكان بدلاً من design tokens (M3) — أكثر صفحة مخالفة | متوسطة |
| CL2 | `teal-*` accent في stats bar (hover:border-teal-200, bg-teal-50) — خارج palette | منخفضة |
| CL3 | Bulk toolbar يستخدم `mr-auto` للـ action buttons — LTR direction (M1) | متوسطة |
| CL4 | Toast في JS يستخدم `left-1/2 -translate-x-1/2` — LTR positioning (M1) | متوسطة |
| CL5 | Client limit state: disabled button فقط، لا upgrade CTA مثل projects — تجربة أدنى | متوسطة |
| CL6 | Import modal close button: `top-2 left-2` — LTR (M1) | منخفضة |
| CL7 | JS يستخدم `__x.$data` للوصول لـ Alpine state من خارجه — هش (يمكن ينكسر بـ Alpine 3 updates) | منخفضة |

---

### 10. Wallets — List (`wallets/index.blade.php`)
**التقييم:** ⭐⭐⭐⭐ جيد جداً مع UX مميز

**ما يعمل جيداً:**
- Dual view (cards/table) مع Alpine tab switcher
- Per-currency summary مع `x-stats-card`
- JS dropdown مع fixed positioning لتجنب overflow
- Upgrade banner رأسي أنيق للـ free users (belt-and-suspenders)
- `shadow-pop`, `border-subtle`, `bg-surface` tokens صحيحة في dropdown

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| W1 | كلاسات `dark:` على الـ upgrade banner وعلى table view — بدون dark mode toggle (M2) | منخفضة |
| W2 | Flash success مكرر داخل الصفحة (M5) | منخفضة |
| W3 | Tab switcher يستخدم `mr-auto` — LTR direction (M1) | منخفضة |

---

### 11. Quotes — List (`quotes/index.blade.php`)
**التقييم:** ⭐⭐⭐½ جيد مع تناقض بسيط

**ما يعمل جيداً:**
- `x-page-header`, `x-data-table`, `x-table-th`, `x-empty-state` — component reuse منتظم
- `$quote->ulid` كـ public identifier — أمان جيد

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| Q1 | Stats row يستخدم `dash-card p-4 text-center` raw بدلاً من `x-stats-card` — inconsistent مع invoices | متوسطة |
| Q2 | `teal-700` لحالة "مقبولة" — خارج design tokens الرسمية | منخفضة |
| Q3 | Stats grid `grid-cols-2 lg:grid-cols-5` — لا `sm:` breakpoint، انتقال مفاجئ | منخفضة |

---

### 12. Projects — List (`projects/index.blade.php`)
**التقييم:** ⭐⭐⭐⭐ جيد مع upgrade card ممتاز

**ما يعمل جيداً:**
- View toggle (cards/table) محفوظ في localStorage
- `x-page-header` مع actions slot
- Premium Upgrade Card (T14) مطبّق بشكل صحيح تماماً
- `@can` / `@cannot` gating صحيح

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| P1 | localStorage للـ view preference — يعمل لكن يمكن يسبب flash عند تحميل الصفحة (hydration mismatch مع Alpine) | منخفضة |

---

### 13. Billing — Index (`billing/index.blade.php`)
**التقييم:** ⭐⭐⭐ متوسط — المنطق جيد لكن التصميم منقوص

**ما يعمل جيداً:**
- Current plan banner للـ active subscribers
- Manual upgrade CTA عبر WhatsApp
- `x-page-header` غير مستخدم لكن العنوان مكتوب مباشرة

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| BI1 | كلاسات `dark:` في كل مكان بدون dark mode toggle (M2) | منخفضة |
| BI2 | Flash messages تستخدم `emerald` و`blue` hardcoded بدلاً من design tokens | منخفضة |
| BI3 | لا يوجد `x-page-header` — العنوان flat | منخفضة |

---

### 14. Billing — Upgrade (`billing/upgrade.blade.php`)
**التقييم:** ⭐⭐⭐½ جيد في المحتوى، مشاكل في التفاصيل

**ما يعمل جيداً:**
- خطوات الترقية اليدوية واضحة (١ → ٢ → ٣)
- معادلات العملات المحلية (ريال / دينار / شيكل)
- Pro highlighted ببادج "الأكثر شيوعاً"
- WhatsApp pre-filled message يشمل البريد + الخطة + السعر

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| BU1 | كلاسات `dark:` في كل مكان بدون dark mode toggle (M2) | منخفضة |
| BU2 | رابط العودة `← العودة` — LTR arrow في RTL (M4) | منخفضة |
| BU3 | Close button في modal: `absolute top-4 left-4` — LTR (M1) | منخفضة |
| BU4 | `max-w-3xl mx-auto` مباشرة — لا `x-page-header` | منخفضة |

---

### 15. Settings (`settings/index.blade.php`)
**التقييم:** ⭐⭐⭐½ جيد مع ملاحظتين

**ما يعمل جيداً:**
- Tabs مع Alpine + hash routing (`window.location.hash`)
- Tab sections: Profile / Invoice / Security / Preferences / Plan
- `dash-field` / `dash-field-error` tokens مستخدمة

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| SE1 | Tab labels تستخدم emoji (👤 🧾 🔒 ⚙️ 💼) — inconsistent مع SVG icons pattern في باقي الواجهة | متوسطة |
| SE2 | Avatar هو initials فقط — لا خيار رفع صورة (feature gap لا bug) | منخفضة |
| SE3 | لا يستخدم `x-page-header` | منخفضة |

---

### 16. Auth — Login (`auth/login.blade.php`)
**التقييم:** ⭐⭐⭐ يعمل لكن على نظام tokens مختلف

**ما يعمل جيداً:**
- RTL عبر `x-guest-layout`
- Validation errors واضحة
- "نسيت كلمة المرور" موجود

**مشاكل:**
| | المشكلة | الأولوية |
|--|---------|---------|
| AU1 | يستخدم `text-gray-*` بدلاً من `text-slate-*` أو design tokens — نظام ألوان مختلف عن باقي التطبيق | متوسطة |
| AU2 | Input fields تستخدم `rounded-xl` بدلاً من `rounded-btn` — تناقض بسيط | منخفضة |
| AU3 | لا يوجد `focus-visible:` styles صريحة للـ accessibility | منخفضة |

---

## ملخص تصنيف المشاكل

### 🔴 عالية الأولوية (تؤثر على UX مباشرة)
| الكود | الصفحة | المشكلة |
|-------|--------|---------|
| IS1 | Invoices Show | أزرار emoji بدلاً من SVG icons |
| IS2 | Invoices Show | `text-left` في صفحة RTL — محاذاة معكوسة |
| IS5 | Invoices Show | `grid-cols-2` بلا responsive — ضيق على موبايل |

### 🟡 متوسطة الأولوية (تأثير على consistency أو accessibility)
| الكود | الصفحة | المشكلة |
|-------|--------|---------|
| A1/N2 | Layout/Navbar | Success flash لا يظهر على desktop في body |
| N1 | Navbar | Dropdown `left-0` بدلاً من `start-0` |
| CL1 | Clients | `text-slate-*` بدلاً من design tokens |
| CL3/CL4 | Clients | LTR positioning في bulk toolbar وtoast |
| CL5 | Clients | Client limit: disabled button فقط بلا upgrade CTA |
| R1 | Reports | Header لا يستخدم `x-page-header` |
| R4 | Reports | Upgrade popover `right-0` بدلاً من `start-0` |
| Q1 | Quotes | Raw div بدلاً من `x-stats-card` |
| T1 | Transactions | `text-left` على header المبلغ |
| IS3 | Invoices Show | `teal-600` خارج نظام الألوان |
| SE1 | Settings | Emoji في tab labels |
| AU1 | Auth | `text-gray-*` بدلاً من design tokens |

### 🟢 منخفضة الأولوية (تحسينات جمالية)
| الكود | الصفحة | المشكلة |
|-------|--------|---------|
| M2 | عام | كلاسات `dark:` ميتة |
| M4 | عام | أسهم LTR في RTL |
| M5 | عام | تكرار flash messages |
| D1/D2 | Dashboard | `text-[13px]` وسهم LTR |
| W1/W2/W3 | Wallets | dark: + flash + RTL direction |
| IS4 | Invoices Show | `slate-*` hardcoded |
| T2/T3/T4 | Transactions | `ml-3`, `slate-600`, `confirm()` |
| Q2/Q3 | Quotes | teal-700, no sm: breakpoint |
| BU1-BU4 | Billing Upgrade | dark:, LTR arrows |
| BI1-BI3 | Billing Index | dark:, hardcoded colors |
| AU2/AU3 | Auth | rounded-xl, no focus-visible |

---

## خطة التحسين المقترحة (page by page)

> ⚠️ لا يبدأ أي تنفيذ قبل موافقة المستخدم على هذه الخطة.

### المرحلة 1 — إصلاح الصفحات العالية الأولوية
**الوقت المقدّر:** جلسة واحدة

**1.1 — `invoices/show.blade.php`** (الأكبر تأثيراً)
- استبدال emoji labels بـ SVG icons مع `aria-label` (IS1)
- تغيير `text-left` إلى `text-start` (IS2)
- `grid grid-cols-1 md:grid-cols-2 gap-8` للقسم invoice paper (IS5)
- استبدال `teal-600` بـ `bg-brand` أو `text-success-700` (IS3)
- استبدال `slate-*` hardcoded بـ design tokens (IS4)

### المرحلة 2 — إصلاح consistency ومشاكل متوسطة
**الوقت المقدّر:** جلستان

**2.1 — Flash message على desktop** (A1/N2)
- إزالة `sm:hidden` من success flash في body، أو نقله لـ navbar بشكل موحّد

**2.2 — RTL Logical Properties** (M1 — cross-cutting)
- استبدال `left-0` بـ `start-0` في navbar dropdown
- استبدال `ml-3` بـ `ms-3` في transactions
- استبدال `mr-auto` بـ `ms-auto` في clients toolbar وwallets tab
- استبدال `left-1/2` بـ `left-1/2` (CSS logical يصعب في Tailwind) أو استخدام `mx-auto` في JS toast

**2.3 — Reports header** (R1/R2)
- لف الـ header في `x-page-header` مع actions slot للـ export buttons
- تصحيح `right-0` → `start-0` في upgrade popover

**2.4 — Quotes stats** (Q1)
- استبدال raw `dash-card p-4 text-center` بـ `x-stats-card` components

**2.5 — Clients upgrade CTA** (CL5)
- إضافة upgrade card مشابهة لـ projects/index عند بلوغ حد العملاء

**2.6 — Settings tab labels** (SE1)
- استبدال emoji بـ SVG icons في tab navigation أو حذفها والاكتفاء بالنص

**2.7 — Auth design tokens** (AU1)
- استبدال `text-gray-*` بـ `text-slate-*` أو design tokens المناسبة

### المرحلة 3 — تنظيف عام منخفض الأولوية
**الوقت المقدّر:** جلسة واحدة

**3.1 — إزالة `dark:` الميتة** (M2)
- إما إزالتها كلها، أو تأجيلها لما يُقرر تفعيل dark mode فعلاً

**3.2 — توحيد design tokens** (M3)
- استبدال `emerald`, `teal`, `slate-900` المتفرقة بالـ tokens المناسبة في billing وclients وwallets

**3.3 — أسهم الاتجاه** (M4)
- استبدال `←` في روابط العودة بـ `→` المناسب للـ RTL

**3.4 — إزالة flash مكرر** (M5)
- حذف `@if(session('success'))` من الصفحات التي تعيد عرضه (wallets/index)

---

## ملاحظات للجولة الثانية

هذه الصفحات لم تُراجَع بعد وتحتاج قراءة مستقلة:

- **`projects/_card.blade.php`** — بطاقة المشروع، احتمال مشاكل في RTL direction
- **`quotes/show.blade.php`** — متوقع نفس مشاكل invoices/show (emoji, text-left)
- **`transactions/create.blade.php`** — نماذج الإدخال، تحقق من RTL alignment
- **`clients/show.blade.php`** — صفحة تفاصيل العميل الكاملة
- **`wallets/show.blade.php`** — معاملات الصندوق
- **`marketing/*`** — pricing, features, contact — تحقق من pricing.blade.php بعد إصلاح B01/B02
- **`auth/register`** — نفس مشاكل auth/login المتوقعة

---

## خلاصة

| الفئة | العدد |
|-------|------|
| صفحات ممتازة (4+ نجوم) | 5 |
| صفحات جيدة (3-4 نجوم) | 8 |
| صفحات تحتاج مراجعة (< 3 نجوم) | 3 |
| مشاكل عالية الأولوية | 3 |
| مشاكل متوسطة الأولوية | 12 |
| مشاكل منخفضة الأولوية | 15+ |

**أبرز ما يُعقد التطبيق:** مشكلة RTL physical vs logical properties تتكرر في 6 صفحات، وكلاسات `dark:` ميتة في 4 صفحات، وعدم اتساق design tokens في 5 صفحات.

**أفضل صفحة في التطبيق:** `dashboard.blade.php` و`invoices/index.blade.php` — نموذج يُحتذى به في استخدام الكومبوننتس والـ design tokens.

**أسوأ صفحة:** `invoices/show.blade.php` — تجمع أكبر عدد من المشاكل ذات التأثير المباشر على المستخدم.
