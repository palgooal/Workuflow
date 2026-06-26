# Production Checklist — دراهم v1.0

> أكمل كل بند وضع ✅ قبل الإطلاق. لا تتجاوز أي بند.

---

## 1. البيئة (Environment)

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` مضبوط على الدومين الإنتاجي الفعلي (مثال: `https://darahum.com`)
- [ ] `APP_KEY` مولَّد بـ `php artisan key:generate` ومحفوظ بأمان
- [ ] ملف `.env` غير موجود في git (تحقق: `git status .env`)
- [ ] `SESSION_DRIVER=database` أو `redis` (ليس `file` في production)
- [ ] `CACHE_STORE=redis` أو `database`
- [ ] `LOG_CHANNEL=daily` مع `LOG_LEVEL=error`
- [ ] `DB_CONNECTION` يشير لقاعدة بيانات production

---

## 2. Queues

- [ ] `QUEUE_CONNECTION=database` أو `redis` (ليس `sync`)
- [ ] تأكد أن جدول `jobs` موجود: `php artisan queue:table && php artisan migrate`
- [ ] Supervisor مضبوط لتشغيل queue worker باستمرار:

```ini
; /etc/supervisor/conf.d/darahum-worker.conf
[program:darahum-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/darahum/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/darahum-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start darahum-worker:*
```

- [ ] فحص حالة worker: `supervisorctl status`

---

## 3. Scheduler

- [ ] Cron مضبوط لتشغيل `schedule:run` كل دقيقة:

```bash
# crontab -e (للمستخدم www-data أو root)
* * * * * cd /var/www/darahum && php artisan schedule:run >> /dev/null 2>&1
```

- [ ] تحقق من أن الـ Scheduled Commands مسجَّلة:

```bash
php artisan schedule:list
```

Commands المتوقَّعة:
- `subscriptions:expire` — يومياً 00:10
- `subscriptions:send-expiry-reminders --days=7` — يومياً 09:30

---

## 4. البريد الإلكتروني (Mail)

- [ ] `MAIL_MAILER=smtp`
- [ ] `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` مضبوطة
- [ ] `MAIL_FROM_ADDRESS` يعكس الدومين الرسمي
- [ ] اختبار إرسال:

```bash
php artisan tinker
Mail::raw('Test email from Darahum', fn($m) => $m->to('test@example.com')->subject('Test'));
```

- [ ] تحقق من وصول البريد في الـ inbox (ليس spam)
- [ ] SPF و DKIM و DMARC مضبوطة على DNS

---

## 5. المدفوعات (Togo Production)

- [ ] `TOGO_API_KEY` مفتاح **إنتاج** حقيقي (ليس مفتاح sandbox)
- [ ] `TOGO_RECEIVER_ADDRESS_ID` مُنشأ بـ `php artisan togo:setup-receiver`
- [ ] `BILLING_PROVIDER=togo`
- [ ] Callback URLs تعمل عبر HTTPS:
  - `https://darahum.com/billing/togo/callback`
  - `https://darahum.com/billing/togo/cancel`
- [ ] SSL certificate صالح (تحقق: `curl -I https://darahum.com`)
- [ ] اختبار دفعة حقيقية صغيرة قبل الإطلاق
- [ ] تحقق من أن PaymentOrder يُنشأ في DB عند بدء الـ checkout

---

## 6. الأمان (Security)

- [ ] Rate Limiting مفعَّل:

```php
// في routes/web.php — تحقق من وجود:
Route::middleware(['throttle:6,1'])->group(fn() =>
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
);
```

- [ ] Admin panel محمي بـ middleware (ليس مفتوحاً للجميع)
- [ ] لا كلمات مرور hardcoded في الكود
- [ ] Headers أمان مضبوطة (X-Frame-Options, CSP):

```bash
php artisan route:list | grep admin
```

- [ ] `APP_DEBUG=false` — لا رسائل خطأ تفصيلية للمستخدمين
- [ ] `.htaccess` أو Nginx يمنع الوصول لـ `/storage` و `/.env`
- [ ] قاعدة البيانات لا تقبل اتصالات من خارج السيرفر

---

## 7. النسخ الاحتياطية (Backups)

- [ ] Script النسخ اليومية مضبوط ومُختبَر (`docs/BACKUP-AND-RECOVERY.md` § 1.1)
- [ ] Script نسخ Storage الأسبوعية مضبوط (`docs/BACKUP-AND-RECOVERY.md` § 1.2)
- [ ] Cron entries للنسخ الاحتياطية موجودة
- [ ] اختبر الاسترداد: نجح استرداد نسخة اختبارية على بيئة staging
- [ ] نسخة خارجية (S3 / Backblaze) مضبوطة

---

## 8. الأداء

- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan optimize`
- [ ] Opcache مفعَّل في PHP
- [ ] Gzip مفعَّل في Nginx/Apache
- [ ] Images مضغوطة

---

## 9. المراقبة (Monitoring)

- [ ] Telescope معطَّل في production أو محدود للـ admin فقط:

```php
// TelescopeServiceProvider: gate() يعمل صح
```

- [ ] `/admin` يعرض dashboard بدون أخطاء
- [ ] `Admin → النظام → سجل النشاط` يعمل
- [ ] `Admin → المدفوعات → Callbacks فاشلة` يعمل
- [ ] `storage/logs/laravel.log` لا يحتوي على أخطاء جديدة
- [ ] `storage/logs/subscriptions-expire.log` يُسجَّل يومياً

---

## 10. الصفحات القانونية

- [ ] صفحة سياسة الخصوصية متاحة ومرتبطة في Footer
- [ ] صفحة الشروط والأحكام متاحة
- [ ] صفحة سياسة الاسترداد متاحة
- [ ] شروط الاشتراك واضحة (شهري/سنوي/تلقائي)
- [ ] سياسة الإلغاء واضحة

---

## 11. الاختبار النهائي قبل الإطلاق

```bash
# 1. تنظيف الكاش
php artisan optimize:clear

# 2. تطبيق migrations
php artisan migrate --force

# 3. إعادة بناء الكاش
php artisan optimize

# 4. تحقق من الـ routes
php artisan route:list | grep -E "billing|admin|legal"

# 5. اختبار smoke
curl -I https://darahum.com
curl -I https://darahum.com/login
curl -I https://darahum.com/privacy

# 6. تشغيل scheduler يدوياً للتحقق
php artisan subscriptions:expire --dry-run
```

---

## توقيع الإطلاق

| المسؤول | التاريخ | الملاحظات |
|--------|---------|----------|
|        |         |           |
|        |         |           |

---

*آخر تحديث: يونيو 2026 — Sprint-03 Production Launch Readiness*
