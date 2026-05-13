<?php

use App\Models\Project;
use App\Models\User;
use App\Support\Enums\ProjectType;

// ==================== الوصول والصلاحيات ====================

test('guest cannot access projects', function () {
    $this->get(route('projects.index'))->assertRedirect(route('login'));
});

test('user can view their own projects', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk();
});

test('user cannot view another user project', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('projects.show', $project))
        ->assertNotFound();
});

// ==================== الإنشاء ====================

test('user can create a project', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name'      => 'مشروع تجريبي',
            'type'      => ProjectType::Business->value,
            'currency'  => 'SAR',
            'color'     => '#6366F1',
            'is_active' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'name'    => 'مشروع تجريبي',
        'user_id' => $user->id,
    ]);
});

test('project name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name'     => '',
            'type'     => ProjectType::Business->value,
            'currency' => 'SAR',
            'color'    => '#6366F1',
        ])
        ->assertSessionHasErrors('name');
});

test('free plan user cannot exceed project limit', function () {
    $user = User::factory()->create(['subscription_plan' => 'free']);

    // إنشاء الحد الأقصى من المشاريع (2 للخطة المجانية)
    Project::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('projects.store'), [
            'name'     => 'مشروع إضافي',
            'type'     => ProjectType::Business->value,
            'currency' => 'SAR',
            'color'    => '#6366F1',
        ])
        ->assertForbidden();
});

// ==================== الحذف ====================

test('user can delete their own project', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('projects.destroy', $project))
        ->assertRedirect(route('projects.index'));

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});

test('user cannot delete another user project', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->delete(route('projects.destroy', $project))
        ->assertNotFound();
});
