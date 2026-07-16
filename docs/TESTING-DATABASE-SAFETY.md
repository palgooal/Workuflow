# حماية قاعدة الاختبارات — TestDatabaseGuard

## الحادثة (2026-07-14، وتكرارها لاحقاً)

`php artisan test` نفَّذ ما يعادل `migrate:fresh` (عبر `RefreshDatabase`) على قاعدة
`darahum` المحلية الحقيقية بدل قاعدة اختبار معزولة، فمُسحت كل بياناتها.

**السبب الجذري**: `bootstrap/cache/config.php` (الناتج عن تشغيل سابق لـ
`php artisan config:cache` أو `php artisan optimize` في بيئة local/production)
يُجمِّد قيم `config('database.default')` و`config('database.connections.*')`
على القيم الحقيقية. عند وجود هذا الملف، يتجاهل Laravel تحميل `.env.testing`
ولا يعيد تقييم `env('DB_CONNECTION')`/`env('DB_DATABASE')` من `phpunit.xml`
إطلاقاً — لأن قيم config المخبَّأة تُستخدَم كما هي دون العودة لملف config
الأصلي. النتيجة: الاختبارات "تعتقد" أنها في بيئة testing (لأن `APP_ENV` يُضبَط
مباشرة من PHPUnit عبر عناصر `<env>` في `phpunit.xml`، قبل أي تحميل لـ `.env` —
هذا الجزء دائماً صحيح)، لكنها فعلياً متصلة بقاعدة البيانات الحقيقية.

## الحماية الحالية (متعددة الطبقات)

المنطق كله في مكان واحد: `app/Support/TestDatabaseGuard.php`.

1. **الطبقة الأساسية** — `tests/TestCase.php::createApplication()`: تعمل
   مباشرة بعد اكتمال إقلاع التطبيق وقبل أن تحصل `RefreshDatabase` على أي فرصة
   للتنفيذ (كل اختبارات `tests/Feature` تمر من هنا عبر `tests/Pest.php`).
2. **طبقة ثانية (دفاع متعدد)** — `app/Providers/AppServiceProvider::register()`:
   تغطي أي سياق آخر يعمل بـ `APP_ENV=testing` خارج `Tests\TestCase` (مثل
   `tests/Unit`، أو أي أمر artisan يُشغَّل يدوياً بهذه البيئة).

كلا الطبقتين تستدعيان `TestDatabaseGuard::assertSafe($driver, $databaseName)` —
يتحقق من `DB::connection()->getDatabaseName()` **الفعلي** (وليس `config()` الذي
قد يكون غير موثوق)، ويرفض أي قاعدة ليست:
- `sqlite` مع قاعدة `:memory:`، أو
- أي اتصال آخر تنتهي قاعدته بـ `_testing` (مثال: `darahum_testing`).

رسالة الرفض تُظهر اسم القاعدة الحالية (driver + database name) والقيم
المقبولة بوضوح.

**قرار تصميم مقصود**: وجود `bootstrap/cache/config.php` (config:cache) **ليس
سبباً للرفض بحد ذاته** — قد يكون مشروعاً تماماً (مثلاً محاكاة إنتاج محلياً).
الفحص يعتمد حصراً على اسم قاعدة البيانات **الفعلي** المتَّصل به، بغض النظر عن
مصدره (سواء جاء من `.env` مباشرة أو من config مخبَّأ) — فلو كانت هذه القاعدة
آمنة (`darahum_testing` أو `:memory:`) يكتمل التنفيذ بشكل طبيعي حتى مع
config:cache مفعّلاً، ولو كانت غير آمنة يُرفَض فوراً بغض النظر عن سبب وصول
القيمة الخاطئة.

## الإعداد الافتراضي (الموصى به — لا يحتاج أي إجراء إضافي)

`.env.testing`/`phpunit.xml` يضبطان `DB_CONNECTION=sqlite` و`DB_DATABASE=:memory:`
— قاعدة في الذاكرة فقط، لا يمكنها لمس أي ملف حقيقي بالتعريف. هذا هو الإعداد
الحالي ويعمل تلقائياً دون أي تجهيز.

## إعداد بديل اختياري: قاعدة MySQL حقيقية `darahum_testing`

بعض الاختبارات (مثل رفض `SystemBackupService` لأي driver غير mysql) تحتاج
اتصال mysql حقيقي لمحاكاة سيناريوهات لا يوفرها sqlite. إن أردت تشغيل كامل
الاختبارات على mysql بدل sqlite، أنشئ قاعدة منفصلة تماماً بهذا الاسم فقط:

```sql
CREATE DATABASE IF NOT EXISTS darahum_testing
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

ثم عدّل `DB_CONNECTION=mysql` و`DB_DATABASE=darahum_testing` في `.env.testing`
(وفي `phpunit.xml` إن كنت تستخدمه بدل `.env.testing`). **يجب** أن ينتهي اسم
القاعدة بـ `_testing` وإلا سيرفض `TestDatabaseGuard` التنفيذ فوراً — هذا
مقصود، وليس خللاً.

⚠️ لا تجعل `DB_DATABASE` يشير أبداً إلى `darahum` (بدون اللاحقة) في أي ملف بيئة
اختبارات، حتى مؤقتاً.

## إثبات أن الحارس يعمل قبل RefreshDatabase (وليس افتراضاً)

بالرجوع لمصدر Laravel المثبَّت فعلياً في `vendor/laravel/framework`:

1. `Illuminate\Foundation\Testing\TestCase::setUp()` (`TestCase.php:61-64`)
   يستدعي `setUpTheTestEnvironment()` فقط.
2. `Concerns\InteractsWithTestCaseLifecycle::setUpTheTestEnvironment()`
   (`Concerns/InteractsWithTestCaseLifecycle.php:91-110`) تستدعي بالترتيب
   داخل نفس الدالة: أولاً `refreshApplication()` (سطر 96)، ثم `setUpTraits()`
   (سطر 101).
3. `refreshApplication()` (`TestCase.php:71-74`) يستدعي `createApplication()`
   — وهي بالضبط الدالة التي يعمل فيها `TestDatabaseGuard::assertSafe()` في
   `Tests\TestCase`.
4. `setUpTraits()` (`Concerns/InteractsWithTestCaseLifecycle.php:212-218`) هي
   التي تتحقق من استخدام `RefreshDatabase::class` وتستدعي `refreshDatabase()`
   فعلياً.

بما أن الاستدعاءين (`refreshApplication()` ثم `setUpTraits()`) يحدثان بالترتيب
داخل **نفس الدالة** `setUpTheTestEnvironment()`، ولا يوجد أي مسار تنفيذ بديل
يتخطى هذا الترتيب، فإن `createApplication()` (وبالتالي الحارس) يُنفَّذ **دائماً**
قبل أي استدعاء ممكن لـ `refreshDatabase()`.

## اختبار الارتداد (Regression Test)

`tests/Unit/Support/TestDatabaseGuardTest.php` يثبت مباشرة أن `TestDatabaseGuard`
يرفض أي اسم قاعدة لا ينتهي بـ `_testing` (أو `sqlite` غير `:memory:`)، ويقبل
الحالات الآمنة فقط.
