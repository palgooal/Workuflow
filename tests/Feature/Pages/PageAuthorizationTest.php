<?php

use App\Models\Page;
use App\Models\User;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

function makeSuperAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

test('a user without the super_admin role cannot manage pages via the policy', function () {
    $user = User::factory()->create();

    $page = Page::create([
        'title'          => 'صفحة للاختبار',
        'slug'           => 'auth-test-page',
        'page_type'      => PageType::General,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 0,
    ]);

    expect(Gate::forUser($user)->allows('viewAny', Page::class))->toBeFalse();
    expect(Gate::forUser($user)->allows('create', Page::class))->toBeFalse();
    expect(Gate::forUser($user)->allows('update', $page))->toBeFalse();
    expect(Gate::forUser($user)->allows('delete', $page))->toBeFalse();
});

test('a super_admin user can manage pages via the policy', function () {
    $admin = makeSuperAdmin();

    $page = Page::create([
        'title'          => 'صفحة للاختبار 2',
        'slug'           => 'auth-test-page-2',
        'page_type'      => PageType::General,
        'content'        => '<p>محتوى</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 0,
    ]);

    expect(Gate::forUser($admin)->allows('viewAny', Page::class))->toBeTrue();
    expect(Gate::forUser($admin)->allows('create', Page::class))->toBeTrue();
    expect(Gate::forUser($admin)->allows('update', $page))->toBeTrue();
    expect(Gate::forUser($admin)->allows('delete', $page))->toBeTrue();
});

test('a non super_admin user is forbidden from the admin pages screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/pages')->assertForbidden();
});

test('a super_admin user can access the admin pages screen', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)->get('/admin/pages')->assertSuccessful();
});

test('a published legal page cannot be force-deleted, even by a super_admin', function () {
    $page = Page::create([
        'title'          => 'صفحة قانونية منشورة',
        'slug'           => 'auth-test-legal-page',
        'page_type'      => PageType::Legal,
        'content'        => '<p>محتوى قانوني</p>',
        'status'         => PageStatus::Published,
        'show_in_footer' => true,
        'footer_group'   => PageFooterGroup::Legal,
        'sort_order'     => 0,
        'published_at'   => now(),
    ]);

    $admin = makeSuperAdmin();

    expect(Gate::forUser($admin)->allows('forceDelete', $page))->toBeFalse();
    expect($page->canBeForceDeleted())->toBeFalse();
});
