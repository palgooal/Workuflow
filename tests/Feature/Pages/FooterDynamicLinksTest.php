<?php

use App\Models\Page;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;

test('a published page with show_in_footer enabled appears in the footer', function () {
    Page::create([
        'title'          => 'صفحة ظاهرة في الفوتر',
        'slug'           => 'footer-visible-page',
        'page_type'      => PageType::Marketing,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Published,
        'show_in_footer' => true,
        'footer_group'   => PageFooterGroup::Product,
        'sort_order'     => 5,
        'published_at'   => now(),
    ]);

    $this->get('/')->assertOk()->assertSee('صفحة ظاهرة في الفوتر');
});

test('a page with show_in_footer disabled does not appear in the footer', function () {
    Page::create([
        'title'          => 'صفحة مخفية عن الفوتر',
        'slug'           => 'footer-hidden-page',
        'page_type'      => PageType::Marketing,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Published,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 5,
        'published_at'   => now(),
    ]);

    $this->get('/')->assertOk()->assertDontSee('صفحة مخفية عن الفوتر');
});

test('a draft page never appears in the footer even if show_in_footer is true', function () {
    Page::create([
        'title'          => 'مسودة يجب ألا تظهر',
        'slug'           => 'footer-draft-page',
        'page_type'      => PageType::Marketing,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => true,
        'footer_group'   => PageFooterGroup::Company,
        'sort_order'     => 5,
    ]);

    $this->get('/')->assertOk()->assertDontSee('مسودة يجب ألا تظهر');
});
