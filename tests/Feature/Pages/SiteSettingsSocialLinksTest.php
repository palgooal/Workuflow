<?php

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;

test('a social link with enabled=true and a real url is active', function () {
    Setting::setGroup('site', [
        'social_x_enabled'        => true,
        'social_x_url'            => 'https://x.com/darahum',
        'social_facebook_enabled' => false,
        'social_facebook_url'     => '',
    ]);

    $links = SiteSettings::activeSocialLinks();

    expect($links)->toHaveKey('x');
    expect($links['x']['url'])->toBe('https://x.com/darahum');
});

test('a disabled social link does not appear even if it has a url', function () {
    Setting::setGroup('site', [
        'social_linkedin_enabled' => false,
        'social_linkedin_url'     => 'https://linkedin.com/company/darahum',
    ]);

    $links = SiteSettings::activeSocialLinks();

    expect($links)->not->toHaveKey('linkedin');
});

test('an enabled social link with an empty url does not appear', function () {
    Setting::setGroup('site', [
        'social_instagram_enabled' => true,
        'social_instagram_url'     => '',
    ]);

    $links = SiteSettings::activeSocialLinks();

    expect($links)->not->toHaveKey('instagram');
});

test('with no site settings saved at all, no social links are active', function () {
    $links = SiteSettings::activeSocialLinks();

    expect($links)->toBe([]);
});

test('active social links flow through into the rendered footer', function () {
    Setting::setGroup('site', [
        'social_whatsapp_enabled' => true,
        'social_whatsapp_url'     => 'https://wa.me/970500000000',
    ]);

    $this->get('/')->assertOk()->assertSee('wa.me/970500000000', false);
});
