# Backup & Recovery — دراهم v1.0

## نظرة عامة

هذا الدليل يوثّق استراتيجية النسخ الاحتياطية واسترداد البيانات لمنصة دراهم. كل سجل مالي (طلب دفع، اشتراك، فاتورة) يجب أن يكون قابلاً للاسترداد خلال ساعة واحدة من أي حادث.

---

## 1. قاعدة البيانات

### 1.1 نسخة يومية — `mysqldump`

```bash
#!/bin/bash
# /opt/scripts/backup-db.sh
# يُشغَّل يومياً في 02:00 عبر cron

DATE=$(date +%Y-%m-%d_%H-%M)
BACKUP_DIR="/var/backups/darahum/db"
DB_NAME="darahum"
DB_USER="darahum_user"
DB_PASS="${DB_PASSWORD}"   # من environment variable — لا تكتب كلمة المرور مباشرة

mkdir -p "${BACKUP_DIR}"

mysqldump \
  --user="${DB_USER}" \
  --password="${DB_PASS}" \
  --single-transaction \
  --routines \
  --triggers \
  --add-drop-table \
  "${DB_NAME}" \
| gzip > "${BACKUP_DIR}/darahum-db-${DATE}.sql.gz"

# حذف النسخ الأقدم من 30 يوماً
find "${BACKUP_DIR}" -name "*.sql.gz" -mtime +30 -delete

echo "[$(date)] DB backup completed: darahum-db-${DATE}.sql.gz"
```

**Cron entry** (`crontab -e`):
```
0 2 * * * /opt/scripts/backup-db.sh >> /var/log/darahum-backup.log 2>&1
```

---

### 1.2 نسخة أسبوعية — Storage كامل

```bash
#!/bin/bash
# /opt/scripts/backup-storage.sh
# يُشغَّل أسبوعياً (الأحد 03:00)

DATE=$(date +%Y-%m-%d)
BACKUP_DIR="/var/backups/darahum/storage"
APP_PATH="/var/www/darahum"

mkdir -p "${BACKUP_DIR}"

tar -czf "${BACKUP_DIR}/storage-${DATE}.tar.gz" \
  "${APP_PATH}/storage/app/public" \
  "${APP_PATH}/storage/logs"

# حذف النسخ الأقدم من 30 يوماً
find "${BACKUP_DIR}" -name "storage-*.tar.gz" -mtime +30 -delete

echo "[$(date)] Storage backup completed: storage-${DATE}.tar.gz"
```

**Cron entry**:
```
0 3 * * 0 /opt/scripts/backup-storage.sh >> /var/log/darahum-backup.log 2>&1
```

---

### 1.3 سياسة الاحتفاظ

| النوع          | التكرار | مدة الاحتفاظ |
|----------------|---------|--------------|
| قاعدة البيانات | يومي    | 30 يوم       |
| Storage        | أسبوعي  | 30 يوم       |

> **ملاحظة:** احفظ نسخة خارجية واحدة على الأقل (S3 أو Backblaze B2) للحماية من فقدان السيرفر كاملاً.

---

## 2. إجراءات الاسترداد

### 2.1 استرداد قاعدة البيانات كاملة

```bash
# 1. اختر النسخة المطلوبة
ls -lh /var/backups/darahum/db/

# 2. فك الضغط
gunzip -k /var/backups/darahum/db/darahum-db-2026-06-26_02-00.sql.gz

# 3. استرداد
mysql -u darahum_user -p darahum < /var/backups/darahum/db/darahum-db-2026-06-26_02-00.sql

# 4. تنظيف الـ cache
php artisan cache:clear
php artisan config:clear
```

---

### 2.2 استرداد Payment Orders + Subscriptions فقط

في حالة تلف جزئي للبيانات المالية دون الحاجة لاسترداد كامل:

```bash
# استخرج جداول الدفع من نسخة محددة
mysqldump --user=darahum_user --password="${DB_PASSWORD}" \
  --single-transaction \
  --no-create-info \
  darahum payment_orders subscriptions \
| gzip > /tmp/financial-tables-$(date +%Y-%m-%d).sql.gz

# استرداد الجداول في بيئة مختلفة للمراجعة أولاً
mysql -u darahum_user -p darahum_staging < /tmp/financial-tables-2026-06-26.sql
```

---

### 2.3 استرداد `users.subscription_plan`

إذا تعطّل حقل `subscription_plan` للمستخدمين (انفصل عن جدول `subscriptions`):

```bash
# أمر الإصلاح اليدوي — يُزامن subscription_plan مع جدول subscriptions
php artisan tinker
```

```php
// داخل tinker:
use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionPlan;

// صفِّر الجميع للـ Free أولاً
User::query()->update(['subscription_plan' => SubscriptionPlan::Free->value]);

// ثم أعِد تفعيل من لديهم اشتراك نشط
Subscription::where('status', 'active')
    ->where('ends_at', '>', now())
    ->with('user')
    ->get()
    ->each(function ($sub) {
        $sub->user?->update(['subscription_plan' => $sub->plan]);
    });

echo "Done. Active subscriptions restored: " . Subscription::where('status', 'active')->count();
```

---

## 3. أوامر الاسترداد والفحص

### فحص سلامة الاشتراكات

```bash
# فحص بدون تعديل (--dry-run)
php artisan subscriptions:expire --dry-run
php artisan subscriptions:send-expiry-reminders --days=7 --dry-run
```

### تشغيل يدوي لأوامر الـ Scheduler

```bash
# تشغيل انتهاء الاشتراكات يدوياً
php artisan subscriptions:expire

# إرسال تذكيرات الانتهاء
php artisan subscriptions:send-expiry-reminders --days=7

# التحقق من حالة queue
php artisan queue:monitor database

# مسح وإعادة تشغيل failed jobs
php artisan queue:retry all
```

### التحقق من حالة الميغريشنز

```bash
php artisan migrate:status
```

---

## 4. سيناريوهات الطوارئ

### سيناريو A: سيرفر لا يستجيب

1. أوقف الـ queue workers فوراً (`supervisorctl stop all`)
2. أرسل صفحة maintenance: `php artisan down --message="نعمل على تحديث النظام"`
3. افحص `/storage/logs/laravel.log` و `/var/log/darahum-backup.log`
4. استرد آخر نسخة من `/var/backups/darahum/db/`
5. شغّل `php artisan migrate` إذا لزم
6. أعِد تشغيل: `php artisan up`

### سيناريو B: دفعة مالية سُجِّلت مرتين

1. افتح `Admin → المدفوعات → طلبات الدفع`
2. ابحث عن الـ ULID أو `provider_order_id` المكرر
3. أكمل صفحة `Timeline` لمعرفة أي الإجراءين نجح فعلياً
4. استخدم `Mark as Cancelled` على النسخة الخاطئة
5. تحقق من `subscriptions` في DB:

```sql
SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5;
```

### سيناريو C: callback تو-غو فشل (404 / timeout)

1. افتح `Admin → المدفوعات → Callbacks فاشلة`
2. اضغط "View Payload" لفحص البيانات
3. تحقق يدوياً من حالة الطلب في Togo Dashboard
4. إذا تأكّد الدفع: استخدم "تأكيد الدفع" في `طلبات الدفع`
5. اضغط "تم الحل" على الـ callback الفاشل

---

## 5. فحص النسخ الاحتياطية شهرياً

```bash
# أنشئ بيئة مؤقتة
mysql -u root -p -e "CREATE DATABASE darahum_restore_test;"

# استرد أحدث نسخة
gunzip -c /var/backups/darahum/db/$(ls -t /var/backups/darahum/db/ | head -1) \
  | mysql -u root -p darahum_restore_test

# تحقق من عدد السجلات
mysql -u root -p -e "
  USE darahum_restore_test;
  SELECT 'users' as tbl, COUNT(*) FROM users
  UNION SELECT 'payment_orders', COUNT(*) FROM payment_orders
  UNION SELECT 'subscriptions', COUNT(*) FROM subscriptions
  UNION SELECT 'invoices', COUNT(*) FROM invoices;
"

# احذف البيئة المؤقتة
mysql -u root -p -e "DROP DATABASE darahum_restore_test;"
```

---

*آخر تحديث: يونيو 2026 — Sprint-03 Production Launch Readiness*
