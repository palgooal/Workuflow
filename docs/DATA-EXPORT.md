# تصدير بيانات المستخدم ("تنزيل نسخة من بياناتي")

## نظرة عامة

ميزة داخل إعدادات الحساب (`resources/views/settings/index.blade.php`, تبويب "بياناتي")
تتيح للمستخدم طلب نسخة ZIP من بياناته الشخصية فقط. **ليست نسخة كاملة من قاعدة
البيانات وليست أداة Restore** — فقط أرشيف قابل للقراءة (CSV + JSON + مرفقات)
لغرض الاحتفاظ الشخصي أو النقل المستقبلي.

## المكونات

| الطبقة | الملف |
|---|---|
| Migration | `database/migrations/2026_07_15_000001_create_data_export_requests_table.php` |
| Model | `app/Models/DataExportRequest.php` |
| Enum | `app/Support/Enums/DataExportStatus.php` |
| Service (بناء الأرشيف) | `app/Services/DataExport/UserDataExportService.php` |
| Job | `app/Jobs/Export/ExportUserDataJob.php` (queue name: `exports`) |
| Controller | `app/Http/Controllers/Account/DataExportController.php` |
| Notifications | `App\Notifications\DataExportReadyNotification` / `DataExportFailedNotification` |
| أوامر الصيانة | `php artisan exports:purge-expired` (مجدول كل ساعة) |
| Config | `config/backups.php` → `user_export.*` |

## Connection مقابل Queue name (تفادياً لأي التباس)

- **Connection**: القناة التي تُخزَّن فيها الـ jobs فعلياً — `QUEUE_CONNECTION`
  في `.env` (= `database` حالياً، **لم يتغيّر ولم يُلمَس بهذه الميزة**).
- **Queue name**: اسم الطابور *داخل* نفس الـ connection — `ExportUserDataJob`
  مضبوطة صراحةً على `public string $queue = 'exports';` حتى يمكن تشغيل
  worker مخصّص لها بمعزل عن باقي طوابير التطبيق (`default` وغيرها).
- **تشغيل worker يعالج هذا الطابور**:
  ```bash
  php artisan queue:work database --queue=exports
  ```
  أو ضمن نفس worker مشترك مع طوابير أخرى:
  ```bash
  php artisan queue:work database --queue=default,exports,backups
  ```
  راجع [DEPLOY.md](DEPLOY.md) — سطر cron الإنتاج مُحدَّث ليشمل `exports`.

## قواعد العزل الأمنية (لا تُنتهَك أبداً)

1. **كل استعلام في `UserDataExportService` يستخدم `where('user_id', $userId)` صراحةً**،
   حتى للموديلات التي تملك `BelongsToUser` أصلاً. السبب: هذا الكود يعمل داخل
   Job بدون `auth()` نشط، فالاعتماد على Global Scope وحده غير آمن. الموديلات
   التي تُستثنى صراحة من الـ Scope عبر `withoutGlobalScope('user')` هي فقط تلك
   المُستخدِمة للـ trait أصلاً (Client, Project, Transaction, TeamMember, Debt,
   Wallet, WalletTransfer, Category, RecurringTransaction, Budget) — والباقي
   (Invoice, Quote, ClientTag, ClientFollowUp, ClientActivity, ClientAttachment,
   Service) لا تملك الـ Scope من الأساس فتُستعلَم مباشرة.
2. **IDs داخلية تسلسلية (auto-increment) لا تُصدَّر أبداً.** تُستبدَل بـ:
   - ULID الموجود أصلاً (Transaction, Wallet, Project, Debt...).
   - مفتاح طبيعي (Client → `public_id`, ClientTag → `slug`).
   - مرجع محلي مولَّد لهذا التصدير فقط (`SVC-1`, `PS-1`...) للموديلات التي لا
     تملك أياً مما سبق (Service المخصّصة، `project_service` pivot).
3. **مستبعدات صريحة دائماً:** `password`, `remember_token`, أي Sanctum token,
   `Quote.token` (نص صريح غير مُجزَّأ — يُعامَل كمفتاح وصول عام)، مجموعة
   `settings` من نوع `payment` بالكامل (تحتوي `togo_api_key`)، أي حقل provider
   داخلي لبوابات الدفع.
4. راجع `tests/Feature/DataExport/DataExportContentTest.php` قبل أي تعديل على
   قائمة الحقول المصدَّرة — يفشل تلقائياً إن ظهر `token`/كلمة مرور/سر في الناتج.

## الآلية

1. المستخدم يضغط "طلب نسخة من بياناتي" → `DataExportController@store`.
2. تحقق من: لا يوجد طلب نشط (pending/processing) + لم يمرّ أقل من
   `rate_limit_hours` (افتراضياً 24 ساعة، عبر `RateLimiter`) منذ آخر طلب.
3. يُنشأ سجل `data_export_requests` (status=pending) ويُطلَق
   `ExportUserDataJob::dispatch()` على **queue name: `exports`** (على نفس
   connection الافتراضي `database` — لا خلط بينهما، راجع الملاحظة أدناه)، وليس داخل الطلب.
4. الـ Job يبني الأرشيف عبر `UserDataExportService::build()`، يرفعه لـ disk
   `exports` (خاص، `storage/app/private/user-data-exports`، غير public)،
   يُحدِّث الحالة إلى completed مع `expires_at = now()->addHours(72)`،
   ويُرسل إشعار database للمستخدم.
5. رابط التنزيل **دائماً Signed URL مؤقت** (`URL::temporarySignedRoute`،
   افتراضياً صالح 60 دقيقة) يُبنى عند الطلب فقط — لا رابط تخزين مباشر أبداً.
   حتى مع رابط صالح التوقيع، الكونترولر يتحقق مرة أخرى صراحة أن
   `file_path` يخص المستخدم المصادَق عليه حالياً (`where('user_id', auth()->id())`).
6. أمر `exports:purge-expired` (مجدول كل ساعة، `routes/console.php`) يحذف
   الملف الفعلي من التخزين ويُعلِّم الطلب `expired` بعد انتهاء `expires_at`.

## الإعدادات القابلة للتعديل (`config/backups.php` → `user_export`)

- `disk` (افتراضياً `exports`، محلي — راجع `config/filesystems.php`)
- `retention_hours` (افتراضياً 72)
- `rate_limit_hours` (افتراضياً 24)
- `signed_url_ttl_minutes` (افتراضياً 60)
- `max_active_requests` (ثابت حالياً = 1)

## ما لم يُنفَّذ عمداً في هذه المرحلة

- **لا استيراد/استعادة من الأرشيف.** الملف للقراءة فقط.
- **لا تشفير للأرشيف نفسه** (بخلاف نسخ النظام) — لأن محتواه بيانات المستخدم
  نفسه فقط، محمي بـ Signed URL + عزل صريح + مسار غير public. إن احتجت لاحقاً
  تشفيره أيضاً، استخدم `App\Services\Backup\BackupEncryptor` (نفس الآلية
  المستخدمة لنسخ النظام).
