<?php

use App\Support\Content\PageContentSanitizer;

test('sanitizer strips script tags', function () {
    $dirty = '<p>مرحباً</p><script>alert("xss")</script>';
    $clean = PageContentSanitizer::clean($dirty);

    expect($clean)->not->toContain('<script');
    expect($clean)->toContain('مرحباً');
});

test('sanitizer strips iframe tags', function () {
    $dirty = '<p>محتوى</p><iframe src="https://evil.example"></iframe>';
    $clean = PageContentSanitizer::clean($dirty);

    expect($clean)->not->toContain('<iframe');
});

test('sanitizer strips inline event attributes', function () {
    $dirty = '<p onclick="alert(1)">اضغط هنا</p>';
    $clean = PageContentSanitizer::clean($dirty);

    expect($clean)->not->toContain('onclick');
});

test('sanitizer keeps safe prose elements intact', function () {
    $dirty = '<h2 id="a">عنوان</h2><p>نص <strong>مهم</strong> و<a href="https://darahum.com">رابط</a>.</p><ul><li>عنصر</li></ul>';
    $clean = PageContentSanitizer::clean($dirty);

    expect($clean)->toContain('<h2')
        ->toContain('<strong>مهم</strong>')
        ->toContain('href="https://darahum.com"')
        ->toContain('<ul>')
        ->toContain('<li>');
});
