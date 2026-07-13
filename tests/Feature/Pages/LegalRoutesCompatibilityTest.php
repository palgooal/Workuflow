<?php

use App\Models\Page;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;

test('old legal routes still resolve and return 200 with no migrated Page rows', function () {
    // بدون أي صف Page — يجب أن تعمل عبر السقوط الآمن (fallback) إلى Blade الثابت.
    $this->get(route('legal.privacy'))->assertOk();
    $this->get(route('legal.terms'))->assertOk();
    $this->get(route('legal.cookies'))->assertOk();
    $this->get(route('legal.data-deletion'))->assertOk();
});

test('legal routes keep their exact urls', function () {
    expect(route('legal.privacy'))->toEndWith('/legal/privacy');
    expect(route('legal.terms'))->toEndWith('/legal/terms');
    expect(route('legal.cookies'))->toEndWith('/legal/cookies');
    expect(route('legal.data-deletion'))->toEndWith('/legal/data-deletion');
});

test('the three out-of-scope legal routes are untouched static views', function () {
    $this->get(route('legal.refund'))->assertOk();
    $this->get(route('legal.subscription-terms'))->assertOk();
    $this->get(route('legal.cancellation'))->assertOk();
});

test('once a matching Page is migrated and published, the legal route serves it instead of the static view', function () {
    $page = Page::create([
        'title'            => 'سياسة الخصوصية (من لوحة الأدمن)',
        'slug'             => 'privacy-policy',
        'page_type'        => PageType::Legal,
        'content'          => '<h2>عنوان مخصص من الأدمن</h2><p>نص مخصص يميّز هذا الاختبار.</p>',
        'excerpt'          => 'ملخص تجريبي',
        'status'           => PageStatus::Published,
        'show_in_footer'   => true,
        'footer_group'     => PageFooterGroup::Company,
        'sort_order'       => 10,
        'document_version' => '1.1.0',
        'published_at'     => now(),
        'last_reviewed_at' => now(),
    ]);

    $this->get(route('legal.privacy'))
        ->assertOk()
        ->assertSee('نص مخصص يميّز هذا الاختبار');
});

test('the generic /pages/{slug} route redirects a legal-type page to its dedicated legal route', function () {
    Page::create([
        'title'            => 'سياسة الخصوصية (من لوحة الأدمن)',
        'slug'             => 'privacy-policy',
        'page_type'        => PageType::Legal,
        'content'          => '<p>نص</p>',
        'status'           => PageStatus::Published,
        'show_in_footer'   => true,
        'footer_group'     => PageFooterGroup::Company,
        'sort_order'       => 10,
        'document_version' => '1.0.0',
        'published_at'     => now(),
        'last_reviewed_at' => now(),
    ]);

    $this->get('/pages/privacy-policy')->assertRedirect(route('legal.privacy'));
});
