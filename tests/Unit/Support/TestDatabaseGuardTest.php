<?php

use App\Support\TestDatabaseGuard;

// ==================== TestDatabaseGuard::assertSafe() ====================
// راجع حادثة 2026-07-14 في app/Support/TestDatabaseGuard.php — هذا الاختبار
// يثبت أن النظام يرفض أي قاعدة بيانات "حقيقية" فوراً، بغض النظر عن السبب
// (config مخبَّأ، خطأ إعداد، إلخ)، بدل أن يسمح لـ RefreshDatabase بلمسها.

test('rejects a real-looking mysql database name', function () {
    expect(fn () => TestDatabaseGuard::assertSafe('mysql', 'darahum'))
        ->toThrow(RuntimeException::class, 'Unsafe database detected');
});

test('rejects a null database name', function () {
    expect(fn () => TestDatabaseGuard::assertSafe('mysql', null))
        ->toThrow(RuntimeException::class);
});

test('rejects sqlite pointing at a real file instead of :memory:', function () {
    expect(fn () => TestDatabaseGuard::assertSafe('sqlite', '/var/www/database.sqlite'))
        ->toThrow(RuntimeException::class);
});

test('rejects a mysql database name that merely contains testing but does not end with it', function () {
    expect(fn () => TestDatabaseGuard::assertSafe('mysql', 'testing_darahum'))
        ->toThrow(RuntimeException::class);
});

test('allows sqlite in-memory database', function () {
    TestDatabaseGuard::assertSafe('sqlite', ':memory:');
})->throwsNoExceptions();

test('allows any real driver when the database name ends with _testing', function () {
    TestDatabaseGuard::assertSafe('mysql', 'darahum_testing');
})->throwsNoExceptions();

test('rejects known-unsafe names explicitly (production, demo)', function (string $name) {
    expect(fn () => TestDatabaseGuard::assertSafe('mysql', $name))
        ->toThrow(RuntimeException::class);
})->with(['darahum', 'production', 'demo']);
