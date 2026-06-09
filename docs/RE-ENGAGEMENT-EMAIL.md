# توثيق — Re-engagement Email

> **آخر تحديث:** 10 يونيو 2026
> **الحالة:** ✅ مكتمل — يحتاج `php artisan migrate` على السيرفر

---

## الهدف

إرسال إيميل "مشروعك الأول ينتظرك 🚀" للمستخدمين الذين سجّلوا ولم ينشئوا أي مشروع أو معاملة.

**الدافع:** من مراجعة `/admin/users` بتاريخ 10/6/2026 — 19 مستخدم مسجّل، ~14 منهم بـ 0 تفعيل.

---

## الملفات

| الملف | الوظيفة |
|-------|---------|
| `database/migrations/2026_06_09_000001_seed_re_engagement_email_template.php` | يُضيف القالب لـ `email_templates` |
| `app/Mail/ReEngagementEmail.php` | Mailable — يستخدم القالب مع المتغيرات |
| `app/Filament/Resources/UserResource.php` | Action فردي + Bulk Action في لوحة الأدمن |

---

## متغيرات القالب

| المتغير | القيمة |
|---------|--------|
| `{{name}}` | اسم المستخدم |
| `{{dashboard_url}}` | `config('app.url') . '/dashboard'` |
| `{{owner_whatsapp}}` | `config('billing.owner_whatsapp')` |

تعديل نص الإيميل: `/admin/email-templates` → مفتاح `re_engagement`.

---

## الاستخدام من لوحة الأدمن

### إرسال فردي
`/admin/users` → صف المستخدم → زر **"إعادة التفعيل"** 🚀 → تأكيد → إرسال.

### إرسال جماعي
`/admin/users` → تحديد المستخدمين المطلوبين (checkbox) → **Actions** → **"إرسال بريد إعادة التفعيل"** → تأكيد → يرسل للكل مع إشعار `✅ أُرسل: X`.

**نصيحة:** فلتر حسب: مشاريع = 0 لتحديد غير المفعّلين تلقائياً.

---

## التفعيل على السيرفر

```bash
php artisan migrate
```

يُضيف قالب `re_engagement` لجدول `email_templates`.

---

## بنية ReEngagementEmail

```php
Mail::to($user->email, $user->name)->send(new ReEngagementEmail($user));
```

يستخدم `emails.template` Blade wrapper (نفس تصميم كل إيميلات دراهم).

---

## مراجع

- `docs/ONBOARDING.md` — سياق مشكلة التفعيل
- `docs/LAUNCH-READINESS-REVIEW.md` — سجل التنفيذ
- `app/Models/EmailTemplate.php` — نظام القوالب
