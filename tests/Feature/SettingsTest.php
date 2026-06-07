<?php

use App\Models\User;

// ==================== الوصول ====================

test('guest cannot access settings', function () {
    $this->get(route('settings.index'))->assertRedirect(route('login'));
});

test('user can view settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.index'))
        ->assertOk();
});

// ==================== تحديث الملف الشخصي ====================

test('user can update their name', function () {
    $user = User::factory()->create(['name' => 'الاسم القديم']);

    $this->actingAs($user)
        ->patch(route('settings.profile'), [
            'name'  => 'الاسم الجديد',
            'email' => $user->email,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id'   => $user->id,
        'name' => 'الاسم الجديد',
    ]);
});

test('cannot use another user email', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);
    $user     = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('settings.profile'), [
            'name'  => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

// ==================== تغيير كلمة المرور ====================

test('user can change password with correct current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('settings.password'), [
            'current_password'      => 'password',
            'password'              => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])
        ->assertRedirect();
});

test('wrong current password is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('settings.password'), [
            'current_password'      => 'wrong-password',
            'password'              => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])
        ->assertSessionHasErrors('current_password');
});

// ==================== التفضيلات ====================

test('user can update preferences', function () {
    $user = User::factory()->create(['currency' => 'SAR', 'timezone' => 'Asia/Riyadh']);

    $this->actingAs($user)
        ->patch(route('settings.preferences'), [
            'currency'          => 'USD',
            'timezone'          => 'Africa/Cairo',
            'target_margin_pct' => 30,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'id'       => $user->id,
        'currency' => 'USD',
        'timezone' => 'Africa/Cairo',
    ]);
});
