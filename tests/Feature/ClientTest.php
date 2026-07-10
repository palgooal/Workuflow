<?php

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Models\ClientTag;

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
            'status'  => 'active',
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
        ->post(route('clients.store'), ['name' => '', 'status' => 'active'])
        ->assertSessionHasErrors('name');
});

test('client status is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), ['name' => 'عميل بدون حالة'])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseMissing('clients', ['name' => 'عميل بدون حالة']);
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

// ==================== الاسم البديل للدفع الإلكتروني (payment_name) ====================

test('payment_name is saved when creating a client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'         => 'أحمد محمد',
            'status'       => 'active',
            'payment_name' => 'Ahmed Mohammed',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'user_id'      => $user->id,
        'name'         => 'أحمد محمد',
        'payment_name' => 'Ahmed Mohammed',
    ]);
});

test('client can be created without payment_name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'   => 'عميل بدون اسم دفع',
            'status' => 'active',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'user_id'      => $user->id,
        'name'         => 'عميل بدون اسم دفع',
        'payment_name' => null,
    ]);
});

test('payment_name is saved when updating a client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create(['payment_name' => null]);

    $this->actingAs($user)
        ->put(route('clients.update', $client->public_id), [
            'name'         => $client->name,
            'payment_name' => 'Updated Payment Name',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'id'           => $client->id,
        'payment_name' => 'Updated Payment Name',
    ]);
});

test('payment_name must be ascii', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'         => 'عميل',
            'status'       => 'active',
            'payment_name' => 'أحمد', // غير ASCII — مرفوض
        ])
        ->assertSessionHasErrors('payment_name');
});

// ==================== ملكية الوسوم عند الإنشاء (tag_ids tenant isolation) ====================

test('user can attach their own tags when creating a client', function () {
    $user = User::factory()->create();
    $tag  = ClientTag::create([
        'user_id'   => $user->id,
        'name'      => 'VIP',
        'slug'      => 'vip-'.$user->id,
        'color'     => '#4F46E5',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'    => 'عميل بوسم خاص',
            'status'  => 'active',
            'tag_ids' => [$tag->id],
        ])
        ->assertRedirect();

    $client = Client::where('user_id', $user->id)->where('name', 'عميل بوسم خاص')->firstOrFail();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user can attach system tags when creating a client', function () {
    $user = User::factory()->create();
    $tag  = ClientTag::create([
        'user_id'   => null, // وسم نظام مشترك
        'name'      => 'وسم نظام',
        'slug'      => 'system-tag-'.uniqid(),
        'color'     => '#10B981',
        'type'      => 'system',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'    => 'عميل بوسم نظام',
            'status'  => 'active',
            'tag_ids' => [$tag->id],
        ])
        ->assertRedirect();

    $client = Client::where('user_id', $user->id)->where('name', 'عميل بوسم نظام')->firstOrFail();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user cannot attach another users private tag when creating a client', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tag   = ClientTag::create([
        'user_id'   => $owner->id,
        'name'      => 'خاص بالمالك',
        'slug'      => 'private-'.$owner->id,
        'color'     => '#EF4444',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($other)
        ->post(route('clients.store'), [
            'name'    => 'محاولة تسريب وسم',
            'status'  => 'active',
            'tag_ids' => [$tag->id],
        ])
        ->assertSessionHasErrors('tag_ids.0');

    $this->assertDatabaseMissing('clients', [
        'user_id' => $other->id,
        'name'    => 'محاولة تسريب وسم',
    ]);
});

// ==================== حد عدد العملاء حسب الخطة (Plan Limits) ====================

test('archived clients do not count toward the free plan client limit', function () {
    $user = User::factory()->create(['subscription_plan' => 'free']);

    Client::factory()->for($user)->count(5)->archived()->create();
    Client::factory()->for($user)->count(4)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'   => 'عميل نشط خامس',
            'status' => 'active',
        ])
        ->assertRedirect(route('clients.index'));

    $this->assertDatabaseHas('clients', [
        'user_id' => $user->id,
        'name'    => 'عميل نشط خامس',
    ]);
});

test('active client count at the free plan limit blocks creation', function () {
    $user = User::factory()->create(['subscription_plan' => 'free']);

    Client::factory()->for($user)->count(5)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->post(route('clients.store'), [
            'name'   => 'عميل سادس مرفوض',
            'status' => 'active',
        ])
        ->assertRedirect()
        ->assertSessionHas('upgrade_prompt');

    $this->assertDatabaseMissing('clients', [
        'user_id' => $user->id,
        'name'    => 'عميل سادس مرفوض',
    ]);
});

test('get clients create redirects with upgrade prompt when at plan limit', function () {
    $user = User::factory()->create(['subscription_plan' => 'free']);

    Client::factory()->for($user)->count(5)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertRedirect()
        ->assertSessionHas('upgrade_prompt');
});

test('get clients create is accessible when under plan limit', function () {
    $user = User::factory()->create(['subscription_plan' => 'free']);

    Client::factory()->for($user)->count(2)->create(['is_archived' => false]);

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertOk();
});
