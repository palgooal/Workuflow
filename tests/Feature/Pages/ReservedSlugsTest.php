<?php

use App\Support\Content\ReservedSlugs;

test('known system slugs are reserved', function (string $slug) {
    expect(ReservedSlugs::isReserved($slug))->toBeTrue();
})->with([
    'login', 'register', 'dashboard', 'admin', 'projects', 'invoices',
    'clients', 'transactions', 'settings', 'api', 'pay', 'pages',
]);

test('a normal future page slug is not reserved', function () {
    expect(ReservedSlugs::isReserved('about-darahum'))->toBeFalse();
    expect(ReservedSlugs::isReserved('careers'))->toBeFalse();
});
