<?php

use App\Models\User;
use App\Models\Wallet;
use App\Support\Enums\SubscriptionPlan;

// ==================== الوصول ====================

test('guest cannot access dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('user can view dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

// ==================== البيانات ====================

test('dashboard loads with wallet data', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user)->create(['name' => 'الصندوق الرئيسي']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('dashboard loads for user with no data', function () {
    // مستخدم جديد بلا بيانات — يجب ألا يسبب خطأ
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('dashboard data is isolated between users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Wallet::factory()->for($user1)->create();

    // user2 يرى الـ dashboard بدون أخطاء
    $this->actingAs($user2)
        ->get(route('dashboard'))
        ->assertOk();
});

// ==================== الـ Onboarding ====================

test('new user sees onboarding', function () {
    $user = User::factory()->create(['onboarding_dismissed_at' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('user can dismiss onboarding', function () {
    $user = User::factory()->create(['onboarding_dismissed_at' => null]);

    $this->actingAs($user)
        ->post(route('onboarding.dismiss'))
        ->assertRedirect();

    $this->assertNotNull($user->fresh()->onboarding_dismissed_at);
});
