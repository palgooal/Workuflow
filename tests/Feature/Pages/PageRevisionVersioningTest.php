<?php

use App\Filament\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function makeVersioningSuperAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

// Livewire::test() يبني مكوّن EditPage مباشرة دون المرور بطلب HTTP فعلي عبر
// Middleware لوحة /admin، لذا Filament::getCurrentPanel() يبقى null ويفشل
// الاستدعاء الداخلي لـ Filament::auth() بخطأ "Call to a member function
// auth() on null". تعيين اللوحة الحالية يدوياً قبل كل اختبار يحلّ هذا.
beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('editing a published legal page snapshots a revision and bumps the document version', function () {
    $admin = makeVersioningSuperAdmin();

    $page = Page::create([
        'title'            => 'سياسة تجريبية',
        'slug'             => 'versioning-test-legal-page',
        'page_type'        => PageType::Legal,
        'content'          => '<p>النص الأصلي قبل التعديل</p>',
        'status'           => PageStatus::Published,
        'show_in_footer'   => true,
        'footer_group'     => PageFooterGroup::Legal,
        'sort_order'       => 0,
        'document_version' => '1.0.0',
        'published_at'     => now()->subMonth(),
        'last_reviewed_at' => now()->subMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'content'     => '<p>النص المعدَّل بعد النشر</p>',
            'change_note' => 'تصحيح صياغة بند التواصل',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->content)->toContain('النص المعدَّل بعد النشر');
    expect($page->document_version)->toBe('1.1.0');
    expect($page->revisions()->count())->toBe(1);

    $revision = $page->revisions()->first();
    expect($revision->version)->toBe('1.0.0');
    expect($revision->content)->toContain('النص الأصلي قبل التعديل');
    expect($revision->change_note)->toBe('تصحيح صياغة بند التواصل');
});

test('editing a published legal page without a change note fails validation', function () {
    $admin = makeVersioningSuperAdmin();

    $page = Page::create([
        'title'            => 'سياسة تجريبية ٢',
        'slug'             => 'versioning-test-legal-page-2',
        'page_type'        => PageType::Legal,
        'content'          => '<p>محتوى أصلي</p>',
        'status'           => PageStatus::Published,
        'show_in_footer'   => true,
        'footer_group'     => PageFooterGroup::Legal,
        'sort_order'       => 0,
        'document_version' => '1.0.0',
        'published_at'     => now()->subMonth(),
        'last_reviewed_at' => now()->subMonth(),
    ]);

    Livewire::actingAs($admin)
        ->test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'content'     => '<p>محتوى معدَّل بدون سبب</p>',
            'change_note' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['change_note']);

    expect($page->fresh()->revisions()->count())->toBe(0);
});

test('editing a draft page does not require a change note or create a revision', function () {
    $admin = makeVersioningSuperAdmin();

    $page = Page::create([
        'title'          => 'صفحة مسودة',
        'slug'           => 'versioning-test-draft-page',
        'page_type'      => PageType::General,
        'content'        => '<p>محتوى مسودة</p>',
        'status'         => PageStatus::Draft,
        'show_in_footer' => false,
        'footer_group'   => PageFooterGroup::None,
        'sort_order'     => 0,
    ]);

    Livewire::actingAs($admin)
        ->test(EditPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'content' => '<p>محتوى مسودة معدَّل</p>',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->content)->toContain('محتوى مسودة معدَّل');
    expect($page->revisions()->count())->toBe(0);
});
