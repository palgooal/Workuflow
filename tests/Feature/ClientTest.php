<?php

use App\Models\Client;
use App\Models\User;

// ==================== الوصول ====================

test('guest cannot access clients', function () {
    $this->get(route('clients.index'))->assertRedirect(route('login'));
});

test('user can view clients page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'    => 'شركة النجاح',
            'email'   => 'info@success.com',
            'phone'   => '0501234567',
            'company' => 'النجاح للتقنية',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'user_id' => $user->id,
        'name'    => 'شركة النجاح',
        'email'   => 'info@success.com',
    ]);
});

test('client name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('client email must be unique per user', function () {
    $user = User::factory()->create();
    Client::factory()->for($user)->create(['email' => 'dup@test.com']);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'  => 'عميل آخر',
            'email' => 'dup@test.com',
        ])
        ->assertSessionHasErrors('email');
});

// ==================== العرض ====================

test('user can view their client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('clients.show', $client->public_id))
        ->assertOk()
        ->assertSee($client->name);
});

test('user cannot view another user client', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    $this->actingAs($other)
        ->get(route('clients.show', $client->public_id))
        ->assertNotFound();
});

// ==================== التعديل ====================

test('user can update their client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('clients.update', $client->public_id), [
            'name'    => 'اسم محدّث',
            'phone'   => '0509999999',
            'company' => 'شركة جديدة',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'id'   => $client->id,
        'name' => 'اسم محدّث',
    ]);
});

test('user cannot update another user client', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    $this->actingAs($other)
        ->put(route('clients.update', $client->public_id), [
            'name' => 'محاولة تعديل',
        ])
        ->assertForbidden();
});

// ==================== الحذف ====================

test('user can delete their client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('clients.destroy', $client->public_id))
        ->assertRedirect();

    $this->assertSoftDeleted('clients', ['id' => $client->id]);
});

test('user cannot delete another user client', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('clients.destroy', $client->public_id))
        ->assertNotFound();
});

// ==================== الأرشفة ====================

test('user can archive their client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->post(route('clients.archive', $client->public_id))
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'id'          => $client->id,
        'is_archived' => true,
    ]);
});
