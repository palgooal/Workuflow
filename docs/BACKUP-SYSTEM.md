# النسخ الاحتياطية التشغيلية (In-App System Backups)

## نظرة عامة

نظام نسخ احتياطي كامل داخل لوحة Filament ("النسخ الاحتياطية"، `super_admin`
فقط)، منفصل تماماً عن [تصدير بيانات المستخدم](DATA-EXPORT.md). يغطي قاعدة
البيانات كاملة، و — لنوع "كاملة" — ملفات storage الضرورية. **هذا يُكمِّل ولا
يُلغي** استراتيجية cron/mysqldump الموثَّقة في
[BACKUP-AND-RECOVERY.md](BACKUP-AND-RECOVERY.md)؛ الفرق أن هذا النظام مُدار من
داخل التطبيق (يمكن تشغيله يدوياً من الواجهة، مُشفَّر تلقائياً، ومُسجَّل في
Activity Log).

## المكونات

| الطبقة | الملف |
|---|---|
| Migration | `database/migrations/2026_07_15_000002_create_backups_table.php` + `2026_07_17_000001_add_triggered_by_to_backups_table.php` |
| Model | `app/Models/Backup.php` |
| Enums | `app/Support/Enums/BackupType.php`, `BackupStatus.php`, `BackupTrigger.php` |
| خدمة الإنشاء | `app/Services/Backup/SystemBackupService.php` |
| التشفير | `app/Services/Backup/BackupEncryptor.php` |
| الاحتفاظ | `app/Services/Backup/BackupRetentionService.php` |
| الجدولة (المرحلة الخامسة) | `app/Services/Backup/ScheduledBackupRunner.php`، `app/Observers/BackupObserver.php`، `app/Filament/Pages/BackupScheduleSettings.php` |
| لوحة المراقبة (المرحلة السادسة) | `app/Services/Backup/BackupMonitoringService.php`، `app/Filament/Widgets/BackupMonitoringWidget.php` |
| Job | `app/Jobs/Backup/RunSystemBackupJob.php` (queue name: `backups`) |
| Filament | `app/Filament/Resources/BackupResource.php` + `Pages/ListBackups.php` |
| تنزيل الأدمن | `app/Http/Controllers/Admin/BackupDownloadController.php` |
| أوامر Artisan | `backup:database`, `backup:full`, `backup:apply-retention`, `backup:restore` (لم تعد `backup:database`/`backup:full` مجدولة تلقائياً — راجع قسم الجدولة أدناه) |
| Config | `config/backups.php` → `system_backup.*` |

## Connection مقابل Queue name (تفادياً لأي التباس)

- **Connection**: `QUEUE_CONNECTION` في `.env` (= `database` حالياً، لم يتغيّر).
- **Queue name**: `RunSystemBackupJob` مضبوطة صراحةً على
  `public string $queue = 'backups';` — طابور مستقل عن `default`/`exports`.
- **تشغيل worker يعالج هذا الطابور**:
  ```bash
  php artisan queue:work database --queue=backups
  ```
  أو ضمن worker مشترك: `php artisan queue:work database --queue=default,exports,backups`
  (هذا ما يستخدمه سطر cron الإنتاج المُحدَّث — راجع [DEPLOY.md](DEPLOY.md)).
  نسخ `full` قد تستغرق دقائق طويلة — إن كثُر استخدامها يُنصَح بسطر cron/worker
  منفصل لطابور `backups` وحده بمهلة أطول، بدل الاعتماد فقط على السطر المشترك.
- **`DB_QUEUE_RETRY_AFTER`** (في `.env`، افتراضياً `3900` إن لم يُضبَط —
  `config/queue.php` → `connections.database.retry_after`): يجب أن يبقى دائماً
  أعلى من أطول `$timeout` لأي Job فعلي على قناة `database` (`RunSystemBackupJob`
  وحدها تصل لـ `job_timeout+60` ≈ 1860 ثانية افتراضياً). إن كانت القيمة أقل من
  ذلك، تعتبر القناة أن الـ Job "منتهي المهلة" بينما لا يزال يعمل فعلياً،
  فيُعاد جدولته لـ worker آخر وينفَّذ **مرتين بالتوازي على نفس النسخة**.

## آلية الإنشاء

1. **لا تشغيل مباشر داخل HTTP request أبداً.** زر "إنشاء نسخة يدوية" في
   Filament ينشئ سجل `Backup` (status=pending) ثم يُطلِق
   `RunSystemBackupJob::dispatch()` على **queue name: `backups`** (على نفس
   connection الافتراضي `database` — لا خلط بينهما، راجع الملاحظة أدناه). أوامر الجدولة
   (`backup:database`/`backup:full`) تفعل الشيء نفسه.
2. `RunSystemBackupJob` يستدعي `SystemBackupService::run()`:
   - يرفض العمل فوراً إن كان `BACKUP_ENCRYPTION_KEY` غير معرَّف في `.env`
     (fail-safe — لا نسخة غير مشفَّرة أبداً).
   - `mysqldump` عبر `Illuminate\Support\Facades\Process` (مصفوفة أوامر، بدون
     shell)، كلمة مرور قاعدة البيانات تمرّ عبر متغيّر بيئة `MYSQL_PWD` للعملية
     الفرعية (لا تظهر في `ps aux`).
   - لنوع `full`: يضيف أيضاً `config('backups.system_backup.include_storage_paths')`
     (افتراضياً مرفقات العملاء + `storage/app/public`)، مع استبعاد صريح لِـ
     `config('backups.system_backup.exclude_patterns')` (cache, sessions,
     views, logs, الملفات المؤقتة).
   - يبني `manifest.json` غير مشفَّر منطقياً (اسم النسخة، النوع، تاريخ الإنشاء،
     إصدار Laravel، قائمة المسارات المُضمَّنة) — **لا يحتوي أي سر أو مفتاح أو
     كلمة مرور مهما كان**، لأنه يُقرأ أثناء الاستعادة قبل توفر أي مفتاح أحياناً.
   - يشفّر الأرشيف الكامل (AES-256-CBC عبر `BackupEncryptor`، مفتاح من
     `BACKUP_ENCRYPTION_KEY` فقط — **لا يُخزَّن المفتاح في أي مكان آخر**، لا في
     الأرشيف ولا في manifest ولا في ملف نصي منفصل).
   - يحسب `checksum` (SHA-256) على الملف **المشفَّر النهائي**، يرفعه لـ disk
     `backups`، ويُحدِّث سجل `Backup` (completed/failed).
   - كل خطوة (إنشاء/تنزيل/حذف/فحص سلامة) تُسجَّل في `activity_logs`.

## التشفير

- خوارزمية: AES-256-CBC، IV عشوائي لكل ملف (يُخزَّن بوضوح في مقدمة الملف —
  ليس سرّياً، لازم فقط لفك التشفير).
- المفتاح: متغيّر بيئة **منفصل تماماً عن `APP_KEY`** — `BACKUP_ENCRYPTION_KEY`
  في `.env`. توليده:
  ```bash
  openssl rand -base64 32
  ```
  أضِفه إلى `.env` على الخادم (production) فقط. **لا تضعه في Git أو في أي مكان
  آخر غير `.env` المحلي على كل خادم.** فقدان هذا المفتاح = فقدان القدرة على فك
  تشفير كل النسخ القديمة — احتفظ بنسخة منه في مدير أسرار (password manager/Vault)
  منفصل عن الخادم.
- الملف المُنزَّل من Filament يبقى **مشفَّراً** (`.zip.enc`) — لا يُفك تشفيره
  على الخادم أبداً عند التنزيل. فك التشفير يحدث فقط محلياً عبر
  `php artisan backup:restore` أو `BackupEncryptor` يدوياً.

## الجدولة (`routes/console.php`) — المرحلة الخامسة: قابلة للتحكم من الإدارة

⚠️ **تغيّر السلوك ابتداءً من المرحلة الخامسة (2026-07-17):** لم تعد نسخة
قاعدة البيانات/الكاملة مجدولتَين بمواعيد ثابتة في الكود. الجدولة الآن طبقة
ديناميكية فوق النظام الحالي، عبر `App\Services\Backup\ScheduledBackupRunner`
(Laravel Scheduler — `Schedule::call()`، وليس Cron يدوي)، وتُقرأ الإعدادات في
كل مرة يعمل فيها `schedule:run` (كل دقيقة عبر سطر cron أدناه) من صفحة:

**الإعدادات → النسخ الاحتياطي** (`app/Filament/Pages/BackupScheduleSettings.php`،
`/admin/backup-schedule-settings`) — تحفظ في جدول `settings` (مجموعة
`backup_schedule`، نفس آلية `Setting::setGroup()` المستخدَمة لإعدادات
البريد/الدفع):

| الإعداد | الافتراضي عند عدم الحفظ | ملاحظة |
|---|---|---|
| تفعيل نسخة قاعدة البيانات (يومياً) | مفعّل | |
| تفعيل النسخة الكاملة (أسبوعياً — الجمعة) | مفعّل | |
| وقت التنفيذ | `02:00` | نفس الوقت لكلا النوعين — النوعان لا يتشاركان يوم التنفيذ (يومي مقابل جمعة فقط) |
| المنطقة الزمنية | `config('app.timezone')` | |
| عدد نسخ الاحتفاظ (يومي/أسبوعي/شهري) | من `config('backups.system_backup.retention.*')` الحالي | **لا يُنشئ إعداداً مكرراً** — يتجاوز نفس مفتاح config في الـruntime عبر `AppServiceProvider::applyBackupScheduleSettings()`؛ `BackupRetentionService` نفسها غير معدَّلة |

**قبل كل تشغيل مجدوَل**: `ScheduledBackupRunner` يتحقق أنه لا توجد أي نسخة
أخرى بحالة `running` (أي نوع) — إن وُجدت، يُسجَّل `Warning` ولا تُنشأ نسخة
جديدة إطلاقاً (منفصل عن `withoutOverlapping()` التي تمنع فقط تداخل نفس
الحدث المجدوَل مع نفسه).

**Logging** (`Log::info/warning/error`، القناة الافتراضية): `Scheduled backup
started`، `Scheduled backup skipped (another backup is already running)`،
`Scheduled backup completed`، `Scheduled backup failed` — الأخيران عبر
`App\Observers\BackupObserver` (يراقب `Backup::updated()` لأي سجل
`triggered_by=scheduled`، بدون أي تعديل على `SystemBackupService`/
`RunSystemBackupJob`).

**مصدر النسخة**: كل سجل `Backup` يحمل الآن `triggered_by` (`manual` أو
`scheduled` — نص وليس Boolean عمداً)، ويظهر كـ badge في عمود "المصدر" داخل
Filament. الأوامر القديمة `backup:database`/`backup:full` ما زالت موجودة
وقابلة للتشغيل يدوياً من CLI دون أي تعديل عليها، لكنها لم تعد جزءاً من
الجدولة الفعلية — الجدولة تستدعي `ScheduledBackupRunner` مباشرة.

| الأمر/الطبقة | التكرار | الوقت |
|---|---|---|
| `ScheduledBackupRunner::run(Database)` | يومي | من الإعدادات (افتراضياً 02:00) |
| `ScheduledBackupRunner::run(Full)` | أسبوعي (الجمعة) | من الإعدادات (نفس الوقت) |
| `backup:apply-retention` | يومي | 05:45 (ثابت — خارج نطاق هذه المرحلة) |
| `exports:purge-expired` | كل ساعة | — |

**متطلب Cron في الإنتاج** (سطر واحد قياسي يكفي لكل جدولة Laravel):
```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## الاحتفاظ (Retention)

`config('backups.system_backup.retention')`: `daily=7`, `weekly=4`, `monthly=3`
(قابلة للتعديل عبر متغيرات env: `BACKUP_RETENTION_DAILY/WEEKLY/MONTHLY`).

السياسة المطبَّقة في `BackupRetentionService`:
- نوع `database`: الاحتفاظ بآخر 7 نسخة ناجحة فقط، حذف الباقي (ملف + سجل).
- نوع `full`: نظام Grandfather-Father-Son مبسّط — أحدث 4 نسخة تُحفَظ كـ
  "أسبوعية"، بالإضافة لأقدم نسخة ناجحة في كل شهر من آخر 3 أشهر تُحفَظ كـ
  "شهرية" حتى لو سقطت خارج نطاق الأربع الأسبوعية.
- النسخ الفاشلة (`status=failed`) **لا تُحذَف تلقائياً أبداً** — تبقى لمراجعة
  الأدمن يدوياً من Filament (زر حذف مع Confirmation).

## التخزين

- **حالياً: `local` فقط** (`storage/app/private/system-backups`، غير public،
  لا رابط مباشر). هذا **ليس** حلاً كافياً لـ Disaster Recovery — نسخة على نفس
  الخادم لا تحمي من فشل الخادم نفسه (حريق/سرقة/تلف قرص).
- التصميم جاهز لأي Laravel Filesystem disk آخر عبر `SYSTEM_BACKUP_DISK` +
  `SYSTEM_BACKUP_FILESYSTEM_DRIVER` في `.env`، دون تعديل كود.
- ⚠️ **قرار معلَّق يحتاج موافقة صاحب المنصة قبل التفعيل:** اختيار مزوّد
  التخزين الخارجي (S3-compatible / SFTP / غيره) + تثبيت الحزمة المناسبة
  (`league/flysystem-aws-s3-v3` للـ S3 — غير مثبتة حالياً في `composer.json`).
  إلى حين هذا القرار، `config('backups.system_backup.disk_is_offsite')` تبقى
  `false` وتُعرَض تحذيراً واضحاً في عمود "مكان التخزين" داخل Filament.

## الصلاحيات

- اللوحة الإدارية بالكامل مقيَّدة بـ `User::canAccessPanel()` (دور
  `super_admin` عبر Spatie Permission) — هذا يكفي تقنياً.
- **دفاع إضافي متعمَّد** في `BackupResource::canViewAny()/canAccess()` وفي
  `BackupDownloadController` (`abort_unless(hasRole('super_admin'), 403)`) —
  حتى لو أُضيف دور إداري أضعف لاحقاً على نفس اللوحة، تبقى النسخ محمية تحديداً.
- تنزيل الأرشيف عبر Signed URL (صلاحية 15 دقيقة) + تحقق الدور معاً.

## لوحة المراقبة (Backup Monitoring Dashboard) — المرحلة السادسة، الجزء الأول

Widget عرض فقط في لوحة `/admin` الرئيسية (`App\Filament\Widgets\BackupMonitoringWidget`،
super_admin فقط) — لا Polling ولا JavaScript ولا Live Refresh، وكل الحساب في
`App\Services\Backup\BackupMonitoringService::snapshot()` عبر استعلام Eloquent
واحد فقط لجدول `backups` (بلا SQL خام)، تُشتَق منه كل النتائج عبر Collections
في الذاكرة:

- آخر نسخة ناجحة/فاشلة (عامةً ولكل نوع Database/Full) — بترتيب `completed_at`.
- إحصائيات: عدد Completed/Running/Failed/Database/Full.
- إجمالي المساحة المستخدَمة (`SUM(size_bytes)` عبر Collection، ليس SQL خام).
- النسخة المجدولة القادمة — محسوبة برمجياً من `Setting::group('backup_schedule')`
  (نفس مصدر `ScheduledBackupRunner`/`BackupScheduleSettings`)، **وليس** من
  `schedule:list`.
- **Health Status** (`healthy`/`warning`/`critical`):
  - **Critical**: لا توجد أي نسخة ناجحة إطلاقاً، أو توجد نسخة `running` "عالقة"
    (أقدم من `job_timeout×2` — نفس تعريف `ApplyBackupRetentionCommand::failAbandonedRunningBackups()`).
  - **Warning**: آخر نسخة ناجحة أقدم من 48 ساعة، أو يوجد فشل خلال آخر 24 ساعة.
  - **Healthy**: خلاف ذلك.

⚠️ هذه المرحلة عرض فقط — لا تُضيف أي منطق نسخ/جدولة جديد ولا تُعدَّل
`SystemBackupService`/`RestoreService`/`ScheduledBackupRunner`/`BackupObserver`/
Jobs النسخ الاحتياطي.

## إشعارات فشل النسخ المجدولة (Backup Failure Notifications) — المرحلة السابعة، الجزء الأول

عند فشل نسخة **مجدولة فقط** (`triggered_by=scheduled` و`status` تنتقل فعلياً
إلى `failed`)، يُرسِل `App\Observers\BackupObserver` إشعار **Filament Database
Notification داخلي فقط** (يظهر في جرس الإشعارات داخل `/admin`) لكل مستخدمي
`super_admin` — استعلام واحد فقط (`User::role('super_admin')->get()`). لا
Email/Slack/Telegram/Webhooks/أي قناة خارجية، ولا نسخ ناجحة، ولا نسخ يدوية،
ولا عمليات استعادة تُرسِل أي إشعار في هذه المرحلة.

- **منع التكرار**: يعتمد حصراً على نفس فحص `wasChanged('status')` المستخدَم
  أصلاً للـLogging (لا منطق تكرار إضافي) — أي تحديث لاحق لا يغيّر `status`
  (اسم/حجم/checksum/إلخ) لا يُطلِق إشعاراً ثانياً.
- **المحتوى**: العنوان "فشل إنشاء النسخة الاحتياطية"، والنص يتضمن النوع
  (`BackupType::label()` الموجودة أصلاً) والسبب (`error_message` أو "سبب غير
  معروف." عند الفراغ) والوقت.
- **الرابط**: Action يفتح مباشرة صفحة تفاصيل تلك النسخة
  (`BackupResource::getUrl('view', ['record' => $backup])`)، وليس الصفحة
  الرئيسية.
- **التفعيل**: `->databaseNotifications()` على لوحة `admin` في
  `AdminPanelProvider` (كانت غير مفعَّلة سابقاً — لازمة لعرض أي Database
  Notification في اللوحة، بصرف النظر عن مصدرها).

## الاستعادة (Restore) — راجع RESTORE-RUNBOOK.md

⚠️ **محدَّث:** القاعدة القديمة "لا زر Restore في Filament بأي شكل" لم تعد
سارية — زر "استعادة النسخة" متاح الآن في Filament (`super_admin` فقط)، وهو
واجهة فقط فوق نفس محرك `RestoreService` المستخدَم في `backup:restore` من
CLI، دون أي منطق استعادة مستقل. راجع `docs/RESTORE-RUNBOOK.md` للتفاصيل
الكاملة.
