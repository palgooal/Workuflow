# Admin Subscriptions MVP — خطة التنفيذ

> **الحالة الراهنة:** `SubscriptionResource` موجود بالفعل في `app/Filament/Resources/SubscriptionResource.php`
> مع list/create/edit pages وجزء من الأعمدة والفلاتر والإجراءات.
> هذه الخطة تحدد الثغرات وترتب العمل المتبقي.

---

## 1. ما هو موجود بالفعل ✅

| العنصر | التفاصيل |
|--------|----------|
| Resource | `SubscriptionResource` — list / create / edit |
| أعمدة | user.name، email (كـ description)، plan badge، status badge، payment_provider، starts_at، ends_at + diff |
| فلاتر | plan، status، expiring_soon (7 أيام) |
| إجراءات | تفعيل خطة، تمديد (1-12 شهر)، إلغاء |
| Navigation Badge | عدد الاشتراكات النشطة |
| Search | searchable على user.name فقط |

---

## 2. الثغرات — ما ينقص ❌

### أعمدة مفقودة
- `remaining_days` — الأيام المتبقية (محسوبة لحظياً، غير مخزنة في DB)
- `provider_subscription_id` — مخفي حالياً

### بحث ناقص
- البحث على `user.email` غير مفعّل (فقط `user.name` searchable)

### إجراءات مفقودة
- **إعادة التفعيل (Reactivate)** — تغيير status من cancelled/expired → active دون تغيير الخطة
- **التخفيض للمجاني (Downgrade to Free)** — تغيير plan → free مع إلغاء نشط

### Service ناقص
- `reactivatePlan()` غير موجود في `SubscriptionService`
- `downgradePlan()` غير موجود في `SubscriptionService`

---

## 3. تفاصيل الجدول النهائي

### الأعمدة (بالترتيب)

| العمود | المصدر | ملاحظة |
|--------|--------|--------|
| المستخدم | `user.name` + email كـ description | searchable |
| الخطة | `plan` enum | badge: gray/primary/success |
| الحالة | `status` | badge: success/danger/warning |
| تاريخ البدء | `starts_at` | format: d/m/Y |
| تاريخ الانتهاء | `ends_at` | لون أحمر إذا past |
| الأيام المتبقية | محسوب: `ends_at->diffInDays(now())` | يظهر "—" إذا لا يوجد ends_at أو منتهي |
| Subscription ID | `provider_subscription_id` | toggleable مخفي افتراضياً، copyable |
| مزود الدفع | `payment_provider` | badge |

### الفلاتر

| الفلتر | النوع | القيم |
|--------|-------|-------|
| الخطة | SelectFilter | free / pro / business |
| الحالة | SelectFilter | active / cancelled / expired |
| تنتهي قريباً | Filter (toggle) | ends_at بين الآن و+7 أيام |
| منتهية الصلاحية | Filter (toggle) | status=active AND ends_at < now() |

### البحث
```php
->searchable(['user.name', 'user.email'])
```

---

## 4. الإجراءات (Actions)

### تمديد الاشتراك (موجود — يحتاج تحسين)
- **يظهر عند:** status = active
- **المدخلات:** عدد الأشهر (Select: 1، 2، 3، 6، 12)
- **المنطق:** `SubscriptionService::extendPlan($user, $months)` ← موجود
- **التحقق:** يجب أن يكون الاشتراك نشطاً
- **edge case:** إذا كان ends_at فارغاً → يبدأ التمديد من now()

### إلغاء الاشتراك (موجود — يحتاج تحسين)
- **يظهر عند:** status = active
- **المنطق:** `SubscriptionService::cancelPlan($user)` ← موجود
- **الأثر:** status → cancelled، ends_at = now()، user.subscription_plan → free
- **تأكيد مطلوب:** modal بنص تحذيري واضح
- **edge case:** لا تلغِ اشتراكاً منتهياً بالفعل

### إعادة التفعيل — Reactivate (مفقود ❌)
- **يظهر عند:** status = cancelled OR expired
- **المدخلات:** عدد الأشهر للتجديد (افتراضي: 1)
- **المنطق الجديد في Service:**
  ```php
  public function reactivatePlan(User $user, int $months = 1): Subscription
  {
      $subscription = Subscription::where('user_id', $user->id)
          ->latest()->first();

      if ($subscription) {
          $subscription->update([
              'status'   => 'active',
              'starts_at' => now(),
              'ends_at'  => now()->addMonths($months),
          ]);
      } else {
          $subscription = $this->activatePlan($user, $user->subscription_plan?->value ?? 'pro');
      }

      $user->update(['subscription_plan' => $subscription->plan]);

      return $subscription->fresh();
  }
  ```
- **edge case:** إذا لم يوجد subscription record → أنشئ جديداً بنفس خطة user

### التخفيض للمجاني — Downgrade to Free (مفقود ❌)
- **يظهر عند:** plan ≠ free AND status = active
- **المنطق الجديد في Service:**
  ```php
  public function downgradePlan(User $user): void
  {
      $user->update(['subscription_plan' => SubscriptionPlan::Free]);

      Subscription::where('user_id', $user->id)
          ->active()
          ->update([
              'plan'    => SubscriptionPlan::Free,
              'status'  => 'cancelled',
              'ends_at' => now(),
          ]);
  }
  ```
- **الأثر:** plan → free، status → cancelled، ends_at = now()
- **تأكيد مطلوب:** modal تحذيري
- **edge case:** لا تُطبّق على من هو free بالفعل

---

## 5. قواعد التحقق (Validation)

| الإجراء | التحقق |
|---------|--------|
| تمديد | months بين 1 و24 |
| تمديد | الاشتراك يجب أن يكون active |
| إلغاء | الاشتراك يجب أن يكون active |
| إعادة التفعيل | status يجب أن يكون cancelled أو expired |
| تخفيض | plan لا تكون free بالفعل |

---

## 6. حالات الحافة (Edge Cases)

| الحالة | التعامل |
|--------|---------|
| مستخدم بدون subscription record | إجراءات Reactivate + Extend تنشئ سجلاً جديداً |
| ends_at = null (خطة مجانية دائمة) | عمود "الأيام المتبقية" يعرض "∞" |
| اشتراك active لكن ends_at منتهي | عمود الأيام يعرض "0" بلون أحمر |
| إلغاء اشتراك لمستخدم Business مع team members | MVP: يُلغى دون تحذير — يُضاف تحذير في مرحلة لاحقة |
| تكرار provider_subscription_id | حقل nullable — لا يُفرض uniqueness في MVP |
| مستخدم admin يعدّل اشتراكه | لا قيود في MVP |

---

## 7. ترتيب التنفيذ

### المرحلة 1 — إصلاح الجدول (30 دقيقة)
1. إضافة عمود `remaining_days` (computed في الـ column)
2. إضافة عمود `provider_subscription_id` كـ toggleable + copyable
3. تفعيل البحث على `user.email`

### المرحلة 2 — إضافة الـ Methods للـ Service (20 دقيقة)
4. `reactivatePlan()` في `SubscriptionService`
5. `downgradePlan()` في `SubscriptionService`

### المرحلة 3 — إضافة الإجراءات للـ Resource (30 دقيقة)
6. Action: Reactivate
7. Action: Downgrade to Free
8. تحسين Confirm modal على Cancel (نص أوضح)

### المرحلة 4 — فلتر إضافي (10 دقيقة)
9. فلتر "منتهية وما زالت active" (status=active AND ends_at < now())

---

## 8. الملفات المعنية

| الملف | العمل |
|-------|-------|
| `app/Filament/Resources/SubscriptionResource.php` | إضافة أعمدة + إجراءات |
| `app/Modules/Billing/Services/SubscriptionService.php` | إضافة reactivatePlan + downgradePlan |
| لا migrations جديدة | كل البيانات موجودة في جدول subscriptions |

---

## 9. تقدير الجهد

| المرحلة | الوقت |
|---------|-------|
| إصلاح الجدول | ~30 دقيقة |
| Service methods | ~20 دقيقة |
| Actions جديدة | ~30 دقيقة |
| الفلتر الإضافي | ~10 دقيقة |
| **المجموع** | **~90 دقيقة** |

---

## 10. ما هو خارج الـ MVP

- إشعارات بريد إلكتروني عند الإلغاء أو التمديد
- سجل تدقيق (audit log) لكل إجراء admin
- تصدير Excel للاشتراكات
- إحصائيات MRR / ARR
- أتمتة تجديد منتهي الصلاحية
- Invoices وسندات الدفع
