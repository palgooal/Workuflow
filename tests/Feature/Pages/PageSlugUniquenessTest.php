<?php

use App\Models\Page;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Illuminate\Database\QueryException;

test('duplicate slug is rejected at the database level', function () {
    Page::create([
        'title'          => 'صفحة أولى',
        'slug'           => 'duplicate-slug',
        'page_type'      => PageType::General,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 0,
    ]);

    expect(function () {
        Page::create([
            'title'          => 'صفحة ثانية بنفس الرابط',
            'slug'           => 'duplicate-slug',
            'page_type'      => PageType::General,
            'content'        => '<p>محتوى آخر</p>',
            'status'         => PageStatus::Draft,
            'show_in_footer' => false,
            'footer_group'   => PageFooterGroup::None,
            'sort_order'     => 0,
        ]);
    })->toThrow(QueryException::class);
});
