<?php

use App\Models\Client;
use App\Models\User;
use App\Modules\CRM\Models\ClientTag;

// ==================== Bulk Assign — ملكية الوسوم (tenant isolation) ====================

test('user can bulk-assign their own tags to their own clients', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $tag    = ClientTag::create([
        'user_id'   => $user->id,
        'name'      => 'VIP',
        'slug'      => 'vip-'.$user->id,
        'color'     => '#4F46E5',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.tags.bulk-assign'), [
            'client_ids' => [$client->id],
            'tag_ids'    => [$tag->id],
            'action'     => 'assign',
        ])
        ->assertOk();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user can bulk-assign system tags to their own clients', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $tag    = ClientTag::create([
        'user_id'   => null,
        'name'      => 'وسم نظام',
        'slug'      => 'system-bulk-'.uniqid(),
        'color'     => '#10B981',
        'type'      => 'system',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.tags.bulk-assign'), [
            'client_ids' => [$client->id],
            'tag_ids'    => [$tag->id],
            'action'     => 'assign',
        ])
        ->assertOk();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user cannot bulk-assign another users private tag', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($other)->create();
    $tag    = ClientTag::create([
        'user_id'   => $owner->id,
        'name'      => 'خاص بالمالك',
        'slug'      => 'private-bulk-'.$owner->id,
        'color'     => '#EF4444',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($other)
        ->post(route('clients.tags.bulk-assign'), [
            'client_ids' => [$client->id],
            'tag_ids'    => [$tag->id],
            'action'     => 'assign',
        ])
        ->assertSessionHasErrors('tag_ids.0');

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeFalse();
});

// ==================== Single Assign — ملكية الوسوم (tenant isolation) ====================

test('user can assign their own tag to their own client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $tag    = ClientTag::create([
        'user_id'   => $user->id,
        'name'      => 'مهم',
        'slug'      => 'mohim-'.$user->id,
        'color'     => '#F59E0B',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.tags.assign', ['client' => $client->public_id, 'tag' => $tag->id]))
        ->assertOk();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user can assign a system tag to their own client', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $tag    = ClientTag::create([
        'user_id'   => null,
        'name'      => 'وسم نظام تعيين',
        'slug'      => 'system-assign-'.uniqid(),
        'color'     => '#06B6D4',
        'type'      => 'system',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($user)
        ->post(route('clients.tags.assign', ['client' => $client->public_id, 'tag' => $tag->id]))
        ->assertOk();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeTrue();
});

test('user cannot assign another users private tag to their own client', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($other)->create();
    $tag    = ClientTag::create([
        'user_id'   => $owner->id,
        'name'      => 'خاص بالمالك',
        'slug'      => 'private-assign-'.$owner->id,
        'color'     => '#EF4444',
        'type'      => 'custom',
        'is_active' => true,
        'priority'  => 1,
    ]);

    $this->actingAs($other)
        ->post(route('clients.tags.assign', ['client' => $client->public_id, 'tag' => $tag->id]))
        ->assertForbidden();

    expect($client->tags()->where('client_tags.id', $tag->id)->exists())->toBeFalse();
});
