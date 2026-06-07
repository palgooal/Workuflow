<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;

// ==================== الوصول ====================

test('guest cannot access budgets', function () {
    $this->get(route('budget.index'))->assertRedirect(route('login'));
});

test('user can view budgets page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('budget.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a monthly budget', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create();

    $this->actingAs($user)
        ->post(route('budget.store'), [
            'amount'      => 3000,
            'period'      => 'monthly',
            'month'       => now()->month,
            'year'        => now()->year,
            'category_id' => $category->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('budgets', [
        'user_id'     => $user->id,
        'amount'      => 3000,
        'period'      => 'monthly',
        'month'       => now()->month,
    ]);
});

test('user can create a yearly budget', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('budget.store'), [
            'amount' => 50000,
            'period' => 'yearly',
            'year'   => now()->year,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'period'  => 'yearly',
        'amount'  => 50000,
    ]);
});

test('budget amount is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('budget.store'), [
            'period' => 'monthly',
            'month'  => now()->month,
            'year'   => now()->year,
        ])
        ->assertSessionHasErrors('amount');
});

test('monthly budget requires month', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('budget.store'), [
            'amount' => 2000,
            'period' => 'monthly',
            'year'   => now()->year,
            // month مفقود
        ])
        ->assertSessionHasErrors('month');
});

test('duplicate budget is rejected', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create();

    Budget::factory()->for($user)->create([
        'category_id' => $category->id,
        'period'      => 'monthly',
        'month'       => now()->month,
        'year'        => now()->year,
    ]);

    $this->actingAs($user)
        ->post(route('budget.store'), [
            'amount'      => 5000,
            'period'      => 'monthly',
            'month'       => now()->month,
            'year'        => now()->year,
            'category_id' => $category->id,
        ])
        ->assertSessionHasErrors('duplicate');
});

// ==================== التعديل ====================

test('user can update their budget', function () {
    $user   = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('budget.update', $budget->id), [
            'amount' => 9999,
            'period' => 'monthly',
            'month'  => $budget->month,
            'year'   => $budget->year,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('budgets', [
        'id'     => $budget->id,
        'amount' => 9999,
    ]);
});

test('user cannot update another user budget', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    $this->actingAs($other)
        ->put(route('budget.update', $budget->id), [
            'amount' => 1,
            'period' => 'monthly',
            'month'  => $budget->month,
            'year'   => $budget->year,
        ])
        ->assertNotFound();
});

// ==================== الحذف ====================

test('user can delete their budget', function () {
    $user   = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('budget.destroy', $budget->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('user cannot delete another user budget', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('budget.destroy', $budget->id))
        ->assertNotFound();
});

// ==================== حساب الاستهلاك ====================

test('budget spent amount reflects expense transactions', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->for($user)->expense()->create();
    $wallet   = Wallet::factory()->for($user)->create();
    $budget   = Budget::factory()->for($user)->create([
        'category_id' => $category->id,
        'amount'      => 5000,
        'period'      => 'monthly',
        'month'       => now()->month,
        'year'        => now()->year,
    ]);

    \App\Models\Transaction::create([
        'user_id'          => $user->id,
        'wallet_id'        => $wallet->id,
        'category_id'      => $category->id,
        'type'             => 'expense',
        'amount'           => 2000,
        'description'      => 'اختبار',
        'transaction_date' => now()->toDateString(),
        'currency'         => 'SAR',
    ]);

    expect($budget->fresh()->spentAmount())->toEqual(2000.0);
    expect($budget->fresh()->usagePercentage())->toEqual(40.0);
});
