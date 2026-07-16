<?php

namespace App\Support;

use RuntimeException;

/**
 * TestDatabaseGuard — يمنع تشغيل أي كود اختبارات (RefreshDatabase، migrate،
 * أو أي شيء آخر) على أي قاعدة بيانات ليست قاعدة اختبار معزولة بوضوح.
 *
 * خلفية (حادثة 2026-07-14 / وتكرارها لاحقاً): php artisan test نفَّذ ما يعادل
 * migrate:fresh (عبر RefreshDatabase) على قاعدة darahum المحلية الحقيقية،
 * فمُسحت كل بياناتها. السبب الجذري: bootstrap/cache/config.php (من تشغيل
 * سابق لـ php artisan config:cache أو optimize في بيئة local/production)
 * يُجمِّد قيم config('database.default') وconfig('database.connections.*')
 * على القيم الحقيقية، ويتجاهل Laravel حينها تحميل .env.testing/phpunit.xml
 * تماماً — فتظل الاختبارات "تعتقد" أنها في بيئة testing (لأن APP_ENV يُضبَط
 * من phpunit.xml مباشرة عبر PHPUnit نفسه، قبل أي تحميل لـ .env، فهذا الجزء
 * دائماً صحيح) بينما تتصل فعلياً بقاعدة الإنتاج/التطوير الحقيقية.
 *
 * ⚠️ لماذا الفحص هنا يعتمد على DB::connection()->getDatabaseName() وليس على
 * config('database.default') مباشرة: لأن config() قد تكون مخبَّأة/غير موثوقة
 * (هذا بالضبط ما تسبب بالحادثة). اسم قاعدة البيانات المُتَّصل بها فعلياً هو
 * الحقيقة الوحيدة التي لا يمكن لأي تخبئة config التحايل عليها — لذا هذا الفحص
 * يعمل بشكل صحيح سواء كان config مخبَّأً أم لا. وجود bootstrap/cache/config.php
 * ليس خطأً بحد ذاته وليس سبباً للرفض؛ الخطأ الوحيد الذي نرفضه هو أن تكون
 * القاعدة المتَّصل بها فعلياً غير آمنة، بغض النظر عن السبب.
 *
 * قواعد القبول:
 *  - sqlite مع قاعدة ":memory:" → آمنة دائماً (لا يمكنها لمس أي ملف حقيقي).
 *  - أي اتصال آخر (mysql/mariadb/pgsql/sqlsrv/...) → اسم القاعدة يجب أن ينتهي
 *    بـ "_testing" (مثال: darahum_testing)، وإلا يُرفَض التنفيذ فوراً.
 */
class TestDatabaseGuard
{
    private const SAFE_SUFFIX = '_testing';

    private const SAFE_SQLITE_DATABASE = ':memory:';

    /**
     * يتحقق من أن الاتصال الفعلي بقاعدة البيانات آمن للاختبارات، ويرمي
     * RuntimeException فوراً إن لم يكن كذلك. يجب استدعاؤها قبل أي إمكانية
     * لتنفيذ migrate/RefreshDatabase (راجع Tests\TestCase::createApplication()
     * وApp\Providers\AppServiceProvider::boot()).
     */
    public static function assertSafe(string $driver, ?string $databaseName): void
    {
        if ($driver === 'sqlite' && $databaseName === self::SAFE_SQLITE_DATABASE) {
            return;
        }

        if ($databaseName !== null && str_ends_with($databaseName, self::SAFE_SUFFIX)) {
            return;
        }

        throw new RuntimeException(
            "Unsafe database detected.\n\n".
            'Current database: '.($databaseName ?: '(غير معروف / فارغ)')." (driver: {$driver})\n\n".
            "Tests are only allowed against:\n".
            "- sqlite \":memory:\"\n".
            '- أي قاعدة تنتهي بـ "'.self::SAFE_SUFFIX.'" (مثال: darahum'.self::SAFE_SUFFIX.")\n\n".
            "لا علاقة لهذا الرفض بوجود config:cache من عدمه — الفحص هنا يعتمد فقط على\n".
            "اسم قاعدة البيانات المتَّصل بها فعلياً (DB::connection()->getDatabaseName())،\n".
            "وليس على أي قيمة config. إن كانت هذه القاعدة خاطئة فعلاً، راجع DB_DATABASE\n".
            "في الملف الذي يحدد بيئتك الحالية، أو شغّل php artisan config:clear إن كنت\n".
            'تشك أن هناك config مخبَّأً يشير لقاعدة غير التي تقصدها.'
        );
    }
}
