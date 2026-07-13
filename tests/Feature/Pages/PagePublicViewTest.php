<?php

use App\Models\Page;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;

function makePage(array $overrides = []): Page
{
    return Page::create(array_merge([
        'title'          => 'صفحة تجريبية',
        'slug'           => 'test-page-' . uniqid(),
        'page_type'      => PageType::General,
        'content'        => '<p>محتوى تجريبي</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 0,
    ], $overrides));
}

test('draft page is not visible publicly (404)', function () {
    $page = makePage(['slug' => 'draft-page', 'status' => PageStatus::Draft]);

    $this->get("/pages/{$page->slug}")->assertNotFound();
});

test('published page is visible publicly', function () {
    $page = makePage(['slug' => 'published-page', 'status' => PageStatus::Published]);

    $this->get("/pages/{$page->slug}")->assertOk()->assertSee('صفحة تجريبية');
});

test('archived page returns 404', function () {
    $page = makePage(['slug' => 'archived-page', 'status' => PageStatus::Archived]);

    $this->get("/pages/{$page->slug}")->assertNotFound();
});

test('soft deleted page returns 404 even if it was published', function () {
    $page = makePage(['slug' => 'deleted-page', 'status' => PageStatus::Published]);
    $page->delete();

    $this->get("/pages/{$page->slug}")->assertNotFound();
});

test('non-existent slug returns 404', function () {
    $this->get('/pages/does-not-exist-at-all')->assertNotFound();
});

test('reserved slug always returns 404 even if a page row exists with it', function () {
    // دفاع في العمق: حتى لو تسرّب صف بهذا الـslug (لا يُفترض أن يحدث بفضل
    // التحقق في PageResource)، لا يُعرَض أبداً لأنه محجوز للنظام.
    makePage(['slug' => 'dashboard', 'status' => PageStatus::Published]);

    $this->get('/pages/dashboard')->assertNotFound();
});
