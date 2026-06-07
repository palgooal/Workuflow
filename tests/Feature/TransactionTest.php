<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\Enums\TransactionType;

// ==================== الوصول ====================

test('guest cannot access transactions', function () {
    $this->get(route('transactions.index'))->assertRedirect(route('login'));
});

test('user can view transactions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('transactions.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create income transaction', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 1500.00,
            'currency'         => 'SAR',
            'description'      => 'راتب شهر مايو',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'description' => 'راتب شهر مايو',
        'user_id'     => $user->id,
        'type'        => TransactionType::Income->value,
    ]);
});

test('transaction amount must be positive', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Expense->value,
            'amount'           => -100,
            'currency'         => 'SAR',
            'description'      => 'مصروف',
            'transaction_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('amount');
});

// ==================== الأمان ====================

test('user cannot edit another user transaction', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();

    $tx = Transaction::factory()->create([
        'user_id' => $owner->id,
        'type'    => TransactionType::Income->value,
    ]);

    $this->actingAs($other)
        ->get(route('transactions.edit', $tx))
        ->assertNotFound();
});

test('user cannot delete another user transaction', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $tx = Transaction::factory()->create([
        'user_id' => $owner->id,
        'type'    => TransactionType::Income->value,
    ]);

    $this->actingAs($other)
        ->delete(route('transactions.destroy', $tx))
        ->assertNotFound();
});
