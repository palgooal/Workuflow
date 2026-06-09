# توثيق — Onboarding Modal & Widget

> **آخر تحديث:** 9 يونيو 2026
> **الحالة:** ✅ محسَّن ومفعَّل

---

## الملفات

| الملف | الوظيفة |
|-------|---------|
| `resources/views/components/onboarding-modal.blade.php` | مودال الترحيب (5 خطوات) |
| `resources/views/components/onboarding-widget.blade.php` | Checklist في لوحة التحكم |
| `app/Services/OnboardingService.php` | منطق تتبع الإكمال |
| `app/Http/Controllers/OnboardingController.php` | `dismiss()` method |
| `routes/web.php` | `POST /onboarding/dismiss` |

---

## تدفق Onboarding

```
تسجيل مستخدم جديد
  ↓
onboarding_dismissed_at = null  AND  created_at > 7 أيام
  ↓
Modal يفتح تلقائياً (Alpine.js localStorage check)
  ↓
5 خطوات → المستخدم يختار: يتصرف أو يتخطّى
  ↓
عند الإغلاق: localStorage + sendBeacon لـ /onboarding/dismiss
  ↓
Widget في لوحة التحكم يتابع الإكمال الفعلي (DB queries)
```

---

## الخطوات (Modal)

| الخطوة | المحتوى | CTA |
|--------|---------|-----|
| 1 | ترحيب + اختيار نوع المستخدم (مستقل / شركة) | زر "التالي" |
| 2 | إنشاء مشروع — شرح + 3 نقاط | "أنشئ مشروعك الأول الآن" → `projects.create` |
| 3 | تسجيل معاملة — دخل أو مصروف | "سجّل أول معاملة الآن" → `transactions.create` |
| 4 | إضافة عميل — للفواتير والواتساب | "أضف عميلك الأول الآن" → `clients.create` |
| 5 | جاهز! 🚀 | "أنشئ مشروعك الأول" أو "تخطّي" |

**ملاحظة:** خطوات 2-4 كل منها زر "تخطّي هذه الخطوة ←" للانتقال بدون تنفيذ.

---

## User Type — التخزين

نوع المستخدم المختار في الخطوة 1 يُحفظ في `localStorage` فقط (مفتاح: `onboarding_user_type`).

القيم: `freelancer` | `business`

> **مستقبلاً:** يمكن إضافة `user_type` column للـ `users` table وإرسالها عبر `sendBeacon` عند الانتقال لخطوة 2 لاستخدامها في تخصيص التجربة.

---

## Widget (Checklist في Dashboard)

يعرض 4 خطوات من `OnboardingService`:

| المفتاح | الإكمال |
|---------|---------|
| `create_project` | `User::projects()->exists()` |
| `add_transaction` | `User::transactions()->exists()` |
| `set_budget` | `User::budgets()->exists()` |
| `view_reports` | auto: project + transaction exist |

يُخفى Widget عند: `onboarding_dismissed_at !== null` أو `progress === 100%`.

---

## Alpine.js — الدوال

```javascript
// تُغلق المودال وترسل dismiss للسيرفر
dismiss()

// تحفظ نوع المستخدم في localStorage
setUserType(type)  // 'freelancer' | 'business'

// تُغلق المودال وتنتقل للصفحة
goToPage(url)
```

---

## شرط الإظهار (Blade)

```php
$showOnboarding = app(\App\Services\OnboardingService::class)->shouldShow($user);
```

`shouldShow()` تُرجع `true` إذا:
- `onboarding_dismissed_at === null` (لم يُغلق المستخدم المودال) AND
- `getCompletedCount() < 4` (لم يكمل جميع الخطوات)

**لا يوجد قيد زمني** — المستخدمون القدامى الذين لم يفعّلوا يرون المودال عند دخولهم التالي.

---

## مراجع

- `docs/LAUNCH-READINESS-REVIEW.md` — السياق الاستراتيجي
- `app/Services/OnboardingService.php` — منطق الإكمال
