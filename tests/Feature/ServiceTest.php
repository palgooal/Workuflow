<?php

use App\Models\Service;
use App\Models\User;

// ==================== الإنشاء ====================

test('guest cannot create a service', function () {
    $this->post(route('services.store'), ['name' => 'x', 'name_ar' => 'x'])
        ->assertRedirect(route('login'));
});

test('user can create a service', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('services.store'), [
            'name'    => 'Web Design',
            'name_ar' => 'تصميم ويب',
            'color'   => '#6366F1',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('services', [
        'user_id' => $user->id,
        'name_ar' => 'تصميم ويب',
    ]);
});

test('service name_ar is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('services.store'), [
            'name'    => 'Test',
            'name_ar' => '',
        ])
        ->assertSessionHasErrors('name_ar');
});

// ==================== الإنشاء السريع (JSON) ====================

test('user can quick-create a service and get json response', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('services.quick-store'), [
            'name_ar' => 'استشارات',
        ]);

    $response->assertOk()
        ->assertJsonStructure(['id', 'name_ar']);

    $this->assertDatabaseHas('services', [
        'user_id' => $user->id,
        'name_ar' => 'استشارات',
    ]);
});

// ==================== الحذف ====================

test('user can delete their service', function () {
    $user    = User::factory()->create();
    $service = Service::factory()->for($user)->create(['is_global' => false]);

    $this->actingAs($user)
        ->delete(route('services.destroy', $service->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('user cannot delete a global service', function () {
    $user    = User::factory()->create();
    $service = Service::factory()->for($user)->create(['is_global' => true]);

    $this->actingAs($user)
        ->delete(route('services.destroy', $service->id))
        ->assertRedirect();

    // الخدمة العامة لم تُحذف
    $this->assertDatabaseHas('services', ['id' => $service->id]);
});

test('user cannot delete another user service', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $service = Service::factory()->for($owner)->create(['is_global' => false]);

    $this->actingAs($other)
        ->delete(route('services.destroy', $service->id))
        ->assertRedirect();

    // لم تُحذف لأنها لا تخص المستخدم الحالي
    $this->assertDatabaseHas('services', ['id' => $service->id]);
});
