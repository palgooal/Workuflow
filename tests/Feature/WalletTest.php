<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransfer;
use App\Support\Enums\TransactionType;
use App\Support\Enums\WalletType;

// ==================== الوصول ====================

test('guest cannot access wallets', function () {
    $this->get(route('wallets.index'))->assertRedirect(route('login'));
});

test('user can view wallets page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('wallets.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a wallet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('wallets.store'), [
            'name'            => 'صندوق الكاش',
            'type'            => WalletType::Cash->value,
            'currency'        => 'SAR',
            'initial_balance' => 1000.00,
            'color'           => '#6366f1',
            'is_active'       => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('wallets', [
        'name'    => 'صندوق الكاش',
        'user_id' => $user->id,
        'type'    => WalletType::Cash->value,
    ]);
});

test('wallet name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('wallets.store'), [
            'name'     => '',
            'type'     => WalletType::Cash->value,
            'currency' => 'SAR',
        ])
        ->assertSessionHasErrors('name');
});

test('wallet type must be valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('wallets.store'), [
            'name'     => 'صندوق',
            'type'     => 'invalid_type',
            'currency' => 'SAR',
        ])
        ->assertSessionHasErrors('type');
});

// ==================== التعديل ====================

test('user can update their wallet', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['name' => 'اسم قديم']);

    $this->actingAs($user)
        ->put(route('wallets.update', $wallet), [
            'name'            => 'اسم جديد',
            'type'            => $wallet->type->value,
            'currency'        => $wallet->currency,
            'initial_balance' => $wallet->initial_balance,
            'color'           => $wallet->color,
            'is_active'       => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('wallets', [
        'id'   => $wallet->id,
        'name' => 'اسم جديد',
    ]);
});

test('user can deactivate a wallet', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['is_active' => true]);

    $this->actingAs($user)
        ->put(route('wallets.update', $wallet), [
            'name'            => $wallet->name,
            'type'            => $wallet->type->value,
            'currency'        => $wallet->currency,
            'initial_balance' => $wallet->initial_balance,
            'color'           => $wallet->color,
            'is_active'       => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('wallets', [
        'id'        => $wallet->id,
        'is_active' => false,
    ]);
});

// ==================== الحذف ====================

test('user can delete their wallet', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('wallets.destroy', $wallet))
        ->assertRedirect();

    $this->assertDatabaseMissing('wallets', ['id' => $wallet->id]);
});

// ==================== حساب الرصيد ====================

test('balance reflects initial balance', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 2000]);

    expect($wallet->balance())->toBe(2000.0);
});

test('balance increases with income transactions', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 1000]);

    Transaction::factory()->for($user)->create([
        'wallet_id' => $wallet->id,
        'type'      => TransactionType::Income->value,
        'amount'    => 500,
    ]);

    expect($wallet->balance())->toBe(1500.0);
});

test('balance decreases with expense transactions', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create(['initial_balance' => 1000]);

    Transaction::factory()->for($user)->create([
        'wallet_id' => $wallet->id,
        'type'      => TransactionType::Expense->value,
        'amount'    => 300,
    ]);

    expect($wallet->balance())->toBe(700.0);
});

test('balance accounts for transfers in and out', function () {
    $user    = User::factory()->create();
    $walletA = Wallet::factory()->for($user)->create(['initial_balance' => 1000]);
    $walletB = Wallet::factory()->for($user)->create(['initial_balance' => 500]);

    WalletTransfer::create([
        'user_id'        => $user->id,
        'from_wallet_id' => $walletA->id,
        'to_wallet_id'   => $walletB->id,
        'amount'         => 200,
        'fee'            => 10,
        'transferred_at' => now()->toDateString(),
    ]);

    // walletA: 1000 - 200 (transfer) - 10 (fee) = 790
    expect($walletA->balance())->toBe(790.0);

    // walletB: 500 + 200 (transfer received) = 700
    expect($walletB->balance())->toBe(700.0);
});

// ==================== ربط المعاملات ====================

test('transaction requires wallet_id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 500,
            'currency'         => 'SAR',
            'description'      => 'دخل بدون صندوق',
            'transaction_date' => now()->toDateString(),
            // wallet_id مفقود
        ])
        ->assertSessionHasErrors('wallet_id');
});

test('transaction wallet must belong to user', function () {
    $user        = User::factory()->create();
    $otherUser   = User::factory()->create();
    $otherWallet = Wallet::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 500,
            'currency'         => 'SAR',
            'description'      => 'دخل',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $otherWallet->id,
        ])
        ->assertSessionHasErrors('wallet_id');
});

test('wallet shows its linked transactions', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    Transaction::factory()->for($user)->create([
        'wallet_id'   => $wallet->id,
        'type'        => TransactionType::Income->value,
        'amount'      => 750,
        'description' => 'دخل مرتبط',
    ]);

    $this->actingAs($user)
        ->get(route('wallets.show', $wallet))
        ->assertOk()
        ->assertSee('دخل مرتبط');
});

// ==================== عزل بيانات المستخدمين ====================

test('user cannot view another user wallet', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();

    $this->actingAs($other)
        ->get(route('wallets.show', $wallet))
        ->assertNotFound();
});

test('user cannot edit another user wallet', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();

    $this->actingAs($other)
        ->get(route('wallets.edit', $wallet))
        ->assertNotFound();
});

test('user cannot delete another user wallet', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('wallets.destroy', $wallet))
        ->assertNotFound();
});

test('user only sees their own wallets on index', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    Wallet::factory()->for($user)->create(['name' => 'صندوقي']);
    Wallet::factory()->for($other)->create(['name' => 'صندوق الآخر']);

    $this->actingAs($user)
        ->get(route('wallets.index'))
        ->assertOk()
        ->assertSee('صندوقي')
        ->assertDontSee('صندوق الآخر');
});
