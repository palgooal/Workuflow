<?php

namespace Tests;

use App\Support\TestDatabaseGuard;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * ⚠️ حارس أمان حرِج — راجع App\Support\TestDatabaseGuard وحادثة 2026-07-14
     * قبل تعديل أي شيء هنا.
     *
     * إثبات ترتيب التنفيذ (وليس افتراضاً) — بالرجوع لمصدر Laravel المثبَّت فعلياً:
     *
     * 1) Illuminate\Foundation\Testing\TestCase::setUp() (vendor/laravel/framework/
     *    src/Illuminate/Foundation/Testing/TestCase.php:61-64):
     *      protected function setUp(): void { $this->setUpTheTestEnvironment(); }
     *
     * 2) Concerns\InteractsWithTestCaseLifecycle::setUpTheTestEnvironment()
     *    (نفس المجلد، Concerns/InteractsWithTestCaseLifecycle.php:91-110):
     *      protected function setUpTheTestEnvironment(): void {
     *          ...
     *          if (! $this->app) {
     *              $this->refreshApplication();   // ← يستدعي createApplication() هذه، أولاً
     *              ...
     *          }
     *          $this->setUpTraits();              // ← يُشغِّل RefreshDatabase، ثانياً (بعد السطر أعلاه)
     *          ...
     *      }
     *
     * 3) TestCase::refreshApplication() (TestCase.php:71-74):
     *      protected function refreshApplication() { $this->app = $this->createApplication(); }
     *
     * 4) InteractsWithTestCaseLifecycle::setUpTraits() (نفس الملف، سطر 212-218):
     *      protected function setUpTraits() {
     *          $uses = ...;
     *          if (isset($uses[RefreshDatabase::class])) { $this->refreshDatabase(); }
     *          ...
     *      }
     *
     * الخلاصة: نفس الدالة setUpTheTestEnvironment() تستدعي refreshApplication()
     * (→ createApplication()، حيث يعمل حارسنا) في السطر 96، ثم setUpTraits()
     * (→ refreshDatabase()) في السطر 101 — بترتيب متسلسل صارم داخل نفس الدالة،
     * لا يوجد أي مسار تنفيذ يستدعي RefreshDatabase قبل createApplication(). لا
     * تُزِل هذا الفحص ولا تنقله إلى setUp() (الفئة الأساسية لا تستدعي أي كود
     * إضافي في setUp() نفسها قبل setUpTheTestEnvironment()، فلا فائدة إضافية
     * من وضعه هناك، والمكان الصحيح المضمون هو هنا).
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app->make('db')->connection();

        TestDatabaseGuard::assertSafe(
            $connection->getDriverName(),
            $connection->getDatabaseName(),
        );

        return $app;
    }
}
