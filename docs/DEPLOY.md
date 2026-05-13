# 🚀 دليل النشر على Shared Hosting (cPanel)

> Workuflow — Laravel 12 / PHP 8.2  
> آخر تحديث: مايو 2026

---

## 📋 المتطلبات

قبل البدء تأكد أن الاستضافة تدعم:

| المتطلب | الإصدار |
|---------|---------|
| PHP | 8.2 أو أعلى |
| MySQL | 8.0 أو أعلى |
| mod_rewrite | مُفعَّل |
| SSH Terminal | مُفضَّل (cPanel → Terminal) |
| Cron Jobs | مُفعَّل |

---

## 🗂️ هيكل الملفات على السيرفر

> **مبدأ أمني مهم:** ملفات Laravel يجب أن تكون خارج `public_html`.

```
/home/cpanel_username/
├── workuflow/                  ← رفع ملفات Laravel هنا
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                 ← هذا هو الـ Document Root للـ Subdomain
│   │   ├── index.php
│   │   └── .htaccess
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env                   ← ملف البيئة (أنشئه يدوياً)
│
└── public_html/                ← موقعك الرئيسي (لا تمسّه)
```

---

## 📌 الخطوات التفصيلية

### الخطوة 1 — إنشاء قاعدة البيانات

1. افتح **cPanel → MySQL Databases**
2. أنشئ قاعدة بيانات جديدة: `workuflow`
3. أنشئ مستخدم جديد وكلمة مرور قوية
4. أضف المستخدم لقاعدة البيانات مع **All Privileges**
5. احتفظ بـ: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

---

### الخطوة 2 — رفع ملفات المشروع

#### الطريقة أ — عبر ZIP (الأسرع)

```bash
# على جهازك المحلي — أنشئ ZIP بدون vendor
# على Windows:
# اختر جميع الملفات ما عدا مجلد vendor وnode_modules
# اضغطها كـ workuflow.zip
```

1. افتح **cPanel → File Manager**
2. انتقل إلى `/home/username/`
3. أنشئ مجلد `workuflow`
4. ارفع `workuflow.zip` واستخرجه داخل المجلد

#### الطريقة ب — عبر Git (إذا كان SSH متاحاً)

```bash
# في cPanel → Terminal
cd /home/username/
git clone https://github.com/your-repo/workuflow.git workuflow
```

---

### الخطوة 3 — إعداد الـ Subdomain

1. افتح **cPanel → Subdomains**
2. أنشئ subdomain مثل: `app.yourdomain.com`
3. في خانة **Document Root** أدخل: `workuflow/public`
4. انقر **Create**

> ⚠️ تأكد أن Document Root يشير إلى `workuflow/public` وليس `workuflow`

---

### الخطوة 4 — إعداد ملف .env

1. في **File Manager** انتقل إلى `/home/username/workuflow/`
2. أنشئ ملف `.env` جديد
3. انسخ محتوى `.env.production.example` وعدّل القيم:

```env
APP_NAME=Workuflow
APP_ENV=production
APP_KEY=                   # سيتم توليده في الخطوة 6
APP_DEBUG=false
APP_URL=https://app.yourdomain.com

DB_HOST=localhost
DB_DATABASE=cpanel_username_workuflow
DB_USERNAME=cpanel_username_dbuser
DB_PASSWORD=كلمة_المرور

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=كلمة_مرور_البريد
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

---

### الخطوة 5 — تثبيت Composer

في **cPanel → Terminal**:

```bash
cd /home/username/workuflow

# تحقق من إصدار PHP
php -v

# تثبيت الحزم للإنتاج فقط (بدون dev dependencies)
composer install --no-dev --optimize-autoloader --no-interaction
```

> إذا لم يكن Composer متاحاً: ارفع مجلد `vendor` كاملاً من جهازك المحلي.

---

### الخطوة 6 — تشغيل أوامر Artisan

في **cPanel → Terminal**:

```bash
cd /home/username/workuflow

# 1. توليد APP_KEY
php artisan key:generate

# 2. تشغيل Migrations
php artisan migrate --force

# 3. إنشاء حساب Admin
php artisan db:seed --class=AdminSeeder

# 4. إنشاء Storage symlink
php artisan storage:link

# 5. تحسين الأداء (مهم جداً في الإنتاج)
php artisan optimize
# يشغّل: config:cache + route:cache + view:cache + event:cache

# 6. تنظيف القديم إذا كان موجوداً
php artisan optimize:clear
php artisan optimize
```

---

### الخطوة 7 — إعداد Cron Jobs

افتح **cPanel → Cron Jobs** وأضف المهمة التالية:

**كل دقيقة** (لتشغيل Scheduler و Queue):

```
* * * * * /usr/local/bin/php /home/username/workuflow/artisan schedule:run >> /dev/null 2>&1
```

**معالجة Queue** (كل دقيقتين):

```
*/2 * * * * /usr/local/bin/php /home/username/workuflow/artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```

> 💡 إذا كان مسار PHP مختلفاً: `which php` في Terminal لمعرفته.

---

### الخطوة 8 — ضبط Permissions

```bash
cd /home/username/workuflow

# صلاحيات المجلدات
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# تأكد من ملكية الملفات
chown -R username:username storage
chown -R username:username bootstrap/cache
```

---

### الخطوة 9 — SSL (HTTPS)

1. افتح **cPanel → SSL/TLS → Let's Encrypt**
2. أصدر شهادة مجانية للـ subdomain: `app.yourdomain.com`
3. فعّل **Force HTTPS Redirect**

---

### الخطوة 10 — التحقق النهائي

افتح المتصفح على `https://app.yourdomain.com` وتأكد:

- [ ] الصفحة الرئيسية تعمل
- [ ] تسجيل الدخول يعمل: `https://app.yourdomain.com/login`
- [ ] Admin panel يعمل: `https://app.yourdomain.com/admin`
  - البريد: `admin@workuflow.com`
  - كلمة المرور: `Admin@123` (غيّرها فوراً!)
- [ ] لا يوجد خطأ 500
- [ ] `/up` يُرجع 200

---

## ⚡ أوامر التحديث (عند رفع تغييرات جديدة)

```bash
cd /home/username/workuflow

# 1. تفعيل Maintenance Mode
php artisan down --message="تحديث النظام، نعود قريباً..." --retry=60

# 2. رفع الملفات الجديدة (FTP/Git)
git pull origin main
# أو ارفع الملفات يدوياً

# 3. تثبيت الحزم الجديدة إن وجدت
composer install --no-dev --optimize-autoloader

# 4. تشغيل Migrations الجديدة
php artisan migrate --force

# 5. تحديث الـ Cache
php artisan optimize:clear
php artisan optimize

# 6. رفع Maintenance Mode
php artisan up
```

---

## 🔧 حل المشاكل الشائعة

### خطأ 500 بعد الرفع
```bash
# تحقق من logs
tail -50 storage/logs/laravel.log

# أو عبر cPanel → Error Logs
```

### الصفحات لا تعمل (404)
```bash
# تأكد أن mod_rewrite مُفعَّل
# وأن Document Root يشير إلى public/ وليس الجذر
```

### خطأ في الـ Storage
```bash
php artisan storage:link
chmod -R 775 storage
```

### Queue لا تعمل
```bash
# تحقق من Cron Job
# وتأكد أن QUEUE_CONNECTION=database في .env
php artisan queue:failed    # لرؤية الفاشلة
php artisan queue:retry all # لإعادة المحاولة
```

### Composer خطأ في الذاكرة
```bash
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader
```

---

## 🔒 أمان الإنتاج — تحقق من هذا

| الأمر | الوضع المطلوب |
|-------|-------------|
| `APP_DEBUG` | `false` |
| `APP_ENV` | `production` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_ENCRYPT` | `true` |
| ملف `.env` permissions | `600` (فقط المالك يقرأ) |
| مجلد `storage/` | خارج متناول الزوار |
| Telescope | معطَّل في الإنتاج |

```bash
# تأكد من صلاحيات .env
chmod 600 /home/username/workuflow/.env
```

---

## 📊 بعد النشر — قائمة تحقق

- [ ] تغيير كلمة مرور Admin من `Admin@123`
- [ ] اختبار تسجيل مستخدم جديد
- [ ] اختبار إنشاء مشروع ومعاملة
- [ ] التحقق أن Cron Jobs تعمل (بعد ساعة)
- [ ] فحص `storage/logs/recurring.log`
- [ ] فحص `storage/logs/debt-alerts.log`
- [ ] التحقق أن البريد يُرسَل (اختبار نسيت كلمة المرور)
- [ ] ضبط DNS للـ subdomain

---

*آخر تحديث: مايو 2026*
