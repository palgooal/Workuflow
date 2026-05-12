<?php

use App\Models\Debt;
use App\Models\User;
use App\Support\Enums\DebtStatus;
use App\Support\Enums\DebtType;

// ==================== الوصول ====================

test('guest cannot access debts', function () {
    $this->get(route('debts.index'))->assertRedirect(route('login'));
});

test('user can view debts page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('debts.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a debt', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('debts.store'), [
            'type'       => DebtType::Borrowed->value,
            'party_name' => 'أحمد محمد',
            'amount'     => 5000,
            'currency'   => 'SAR',
        ])
        ->assertRedirect(route('debts.index'));

    $this->assertDatabaseHas('debts', [
        'party_name' => 'أحمد محمد',
        'user_id'    => $user->id,
        'status'     => DebtStatus::Active->value,
    ]);
});

// ==================== تسجيل الدفعة ====================

test('user can record partial payment', function () {
    $user = User::factory()->create();
    $debt = Debt::factory()->create([
        'user_id'          => $user->id,
        'amount'           => 1000,
        'remaining_amount' => 1000,
        'status'           => DebtStatus::Active->value,
        'type'             => DebtType::Borrowed->value,
    ]);

    $this->actingAs($user)
        ->post(route('debts.record-payment', $debt), ['amount' => 400])
        ->assertRedirect(route('debts.index'));

    $this->assertDatabaseHas('debts', [
        'id'               => $debt->id,
        'remaining_amount' => 600,
        'status'           => DebtStatus::PartiallyPaid->value,
    ]);
});

test('payment cannot exceed remaining amount', function () {
    $user = User::factory()->create();
    $debt = Debt::factory()->create([
        'user_id'          => $user->id,
        'amount'           => 1000,
        'remaining_amount' => 300,
        'status'           => DebtStatus::Active->value,
        'type'             => DebtType::Borrowed->value,
    ]);

    $this->actingAs($user)
        ->post(route('debts.record-payment', $debt), ['amount' => 500])
        ->assertSessionHasErrors('amount');
});

test('user can mark debt as fully paid', function () {
    $user = User::factory()->create();
    $debt = Debt::factory()->create([
        'user_id'          => $user->id,
        'amount'           => 2000,
        'remaining_amount' => 2000,
        'status'           => DebtStatus::Active->value,
        'type'             => DebtType::Lent->value,
    ]);

    $this->actingAs($user)
        ->post(route('debts.mark-paid', $debt))
        ->assertRedirect(route('debts.index'));

    $this->assertDatabaseHas('debts', [
        'id'               => $debt->id,
        'remaining_amount' => 0,
        'status'           => DebtStatus::Paid->value,
    ]);
});

// ==================== الأمان ====================

test('user cannot pay another user debt', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $debt  = Debt::factory()->create([
        'user_id'          => $owner->id,
        'amount'           => 1000,
        'remaining_amount' => 1000,
        'status'           => DebtStatus::Active->value,
        'type'             => DebtType::Borrowed->value,
    ]);

    $this->actingAs($other)
        ->post(route('debts.record-payment', $debt), ['amount' => 100])
        ->assertForbidden();
});
