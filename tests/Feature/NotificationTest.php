<?php

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

// ==================== الوصول ====================

test('guest cannot access notifications', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

test('user can view notifications page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk();
});

// ==================== القراءة ====================

test('user can mark notification as read', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id'              => \Illuminate\Support\Str::uuid(),
        'type'            => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $user->id,
        'data'            => json_encode(['message' => 'اختبار', 'link' => null]),
        'read_at'         => null,
    ]);

    $this->actingAs($user)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect();

    $this->assertNotNull(
        $user->notifications()->where('id', $notification->id)->first()?->read_at
    );
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $user->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['message' => "إشعار $i"]),
            'read_at'         => null,
        ]);
    }

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    $this->assertSame(0, $user->unreadNotifications()->count());
});

// ==================== الحذف ====================

test('user can delete a notification', function () {
    $user = User::factory()->create();

    $id = (string) \Illuminate\Support\Str::uuid();
    $user->notifications()->create([
        'id'              => $id,
        'type'            => 'App\Notifications\TestNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $user->id,
        'data'            => json_encode(['message' => 'حذف']),
        'read_at'         => null,
    ]);

    $this->actingAs($user)
        ->delete(route('notifications.destroy', $id))
        ->assertRedirect();

    $this->assertDatabaseMissing('notifications', ['id' => $id]);
});

// ==================== API ====================

test('unread count endpoint returns json', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('notifications.unread-count'))
        ->assertOk()
        ->assertJsonStructure(['count']);
});

test('unread count reflects actual unread notifications', function () {
    $user = User::factory()->create();

    foreach (range(1, 2) as $i) {
        $user->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['message' => "إشعار $i"]),
            'read_at'         => null,
        ]);
    }

    $this->actingAs($user)
        ->getJson(route('notifications.unread-count'))
        ->assertOk()
        ->assertJson(['count' => 2]);
});
