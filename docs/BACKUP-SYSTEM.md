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
| Migration | `database/migrations/2026_07_15_000002_create_backups_table.php` |
| Model | `app/Models/Backup.php` |
| Enums | `app/Support/Enums/BackupType.php`, `BackupStatus.php` |
| خدمة الإنشاء | `app/Services/Backup/SystemBackupService.php` |
| التشفير | `app/Services/Backup/BackupEncryptor.php` |
| الاحتفاظ | `app/Services/Backup/BackupRetentionService.php` |
| Job | `app/Jobs/Backup/RunSystemBackupJob.php` (queue name: `backups`) |
| Filament | `app/Filament/Resources/BackupResource.php` + `Pages/ListBackups.php` |
| تنزيل الأدمن | `app/Http/Controllers/Admin/BackupDownloadController.php` |
| أوامر Artisan | `backup:database`, `backup:full`, `backup:apply-retention`, `backup:restore` |
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

## الجدولة (`routes/console.php`)

| الأمر | التكرار | الوقت |
|---|---|---|
| `backup:database` | يومي | 05:00 |
| `backup:full` | أسبوعي (الجمعة) | 05:30 |
| `backup:apply-retention` | يومي | 05:45 (بعد الاثنين أعلاه) |
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

## ما لم يُنفَّذ عمداً (راجع RESTORE-RUNBOOK.md)

- **لا زر Restore في Filament بأي شكل.** الاستعادة فقط عبر
  `php artisan backup:restore` من CLI بواسطة مسؤول لديه وصول للخادم مباشرة.
