<?php

use App\Models\User;
use App\Support\Enums\SubscriptionPlan;

// ==================== الوصول ====================

test('guest cannot export pdf report', function () {
    $this->get(route('reports.export.pdf'))->assertRedirect(route('login'));
});

test('guest cannot export excel report', function () {
    $this->get(route('reports.export.excel'))->assertRedirect(route('login'));
});

// ==================== خطة مجانية (محظور) ====================

test('free plan user cannot export pdf', function () {
    $user = User::factory()->create(['subscription_plan' => SubscriptionPlan::Free]);

    $this->actingAs($user)
        ->get(route('reports.export.pdf'))
        ->assertForbidden();
});

test('free plan user cannot export excel', function () {
    $user = User::factory()->create(['subscription_plan' => SubscriptionPlan::Free]);

    $this->actingAs($user)
        ->get(route('reports.export.excel'))
        ->assertForbidden();
});

// ==================== خطة Pro (مسموح) ====================

test('pro plan user can export excel', function () {
    $user = User::factory()->create(['subscription_plan' => SubscriptionPlan::Pro]);

    $response = $this->actingAs($user)
        ->get(route('reports.export.excel'));

    // يجب ألا يكون 403 أو 500
    $this->assertContains($response->getStatusCode(), [200, 302]);
});

test('business plan user can export excel', function () {
    $user = User::factory()->create(['subscription_plan' => SubscriptionPlan::Business]);

    $response = $this->actingAs($user)
        ->get(route('reports.export.excel'));

    $this->assertContains($response->getStatusCode(), [200, 302]);
});
