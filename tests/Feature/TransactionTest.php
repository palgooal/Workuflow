<?php

use App\Models\Category;
use App\Models\Project;
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
        'user_id'     => $user->id,
        'description' => 'راتب شهر مايو',
        'type'        => TransactionType::Income->value,
    ]);
});

test('user can create expense transaction', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Expense->value,
            'amount'           => 250.00,
            'currency'         => 'SAR',
            'description'      => 'فاتورة كهرباء',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'description' => 'فاتورة كهرباء',
        'type'        => TransactionType::Expense->value,
    ]);
});

test('transaction amount must be positive', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Expense->value,
            'amount'           => -100,
            'currency'         => 'SAR',
            'description'      => 'مصروف',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertSessionHasErrors('amount');
});

test('transaction description is required', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 500,
            'currency'         => 'SAR',
            'description'      => '',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertSessionHasErrors('description');
});

test('transaction type must be valid', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => 'invalid_type',
            'amount'           => 500,
            'currency'         => 'SAR',
            'description'      => 'اختبار',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertSessionHasErrors('type');
});

test('transaction wallet must belong to user', function () {
    $user        = User::factory()->create();
    $otherWallet = Wallet::factory()->create(); // صندوق مستخدم آخر

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 500,
            'currency'         => 'SAR',
            'description'      => 'اختبار',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $otherWallet->id,
        ])
        ->assertSessionHasErrors('wallet_id');
});

test('user can create transaction with category', function () {
    $user     = User::factory()->create();
    $wallet   = Wallet::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['type' => TransactionType::Income]);

    $this->actingAs($user)
        ->post(route('transactions.store'), [
            'type'             => TransactionType::Income->value,
            'amount'           => 800,
            'currency'         => 'SAR',
            'description'      => 'عمل فريلانس',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
            'category_id'      => $category->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'category_id' => $category->id,
    ]);
});

// ==================== العرض ====================

test('user cannot view another user transaction show page', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();
    $tx     = Transaction::factory()->for($owner)->for($wallet)->create([
        'type' => TransactionType::Income->value,
    ]);

    // BelongsToUser global scope يُعيد 404 قبل أي policy
    $this->actingAs($other)
        ->get(route('transactions.show', $tx))
        ->assertNotFound();
});

// ==================== التعديل ====================

test('user can update their transaction', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();
    $tx     = Transaction::factory()->for($user)->for($wallet)->create([
        'type'        => TransactionType::Income->value,
        'description' => 'قديم',
    ]);

    $this->actingAs($user)
        ->put(route('transactions.update', $tx), [
            'type'             => TransactionType::Income->value,
            'amount'           => 2000,
            'currency'         => 'SAR',
            'description'      => 'وصف محدّث',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $wallet->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'id'          => $tx->id,
        'description' => 'وصف محدّث',
    ]);
});

test('user cannot edit another user transaction', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();
    $tx     = Transaction::factory()->for($owner)->for($wallet)->create([
        'type' => TransactionType::Income->value,
    ]);

    $this->actingAs($other)
        ->get(route('transactions.edit', $tx))
        ->assertNotFound();
});

test('user cannot update another user transaction', function () {
    $owner       = User::factory()->create();
    $other       = User::factory()->create();
    $ownerWallet = Wallet::factory()->for($owner)->create();
    $otherWallet = Wallet::factory()->for($other)->create();
    $tx          = Transaction::factory()->for($owner)->for($ownerWallet)->create([
        'type' => TransactionType::Income->value,
    ]);

    $this->actingAs($other)
        ->put(route('transactions.update', $tx), [
            'type'             => TransactionType::Income->value,
            'amount'           => 9999,
            'currency'         => 'SAR',
            'description'      => 'اختراق',
            'transaction_date' => now()->toDateString(),
            'wallet_id'        => $otherWallet->id,
        ])
        ->assertNotFound();
});

// ==================== الحذف ====================

test('user can delete their transaction', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();
    $tx     = Transaction::factory()->for($user)->for($wallet)->create([
        'type' => TransactionType::Expense->value,
    ]);

    $this->actingAs($user)
        ->delete(route('transactions.destroy', $tx))
        ->assertRedirect();

    $this->assertSoftDeleted('transactions', ['id' => $tx->id]);
});

test('user cannot delete another user transaction', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $wallet = Wallet::factory()->for($owner)->create();
    $tx     = Transaction::factory()->for($owner)->for($wallet)->create([
        'type' => TransactionType::Income->value,
    ]);

    $this->actingAs($other)
        ->delete(route('transactions.destroy', $tx))
        ->assertNotFound();
});

// ==================== عزل البيانات ====================

test('user only sees their own transactions', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    $wallet1 = Wallet::factory()->for($user)->create();
    $wallet2 = Wallet::factory()->for($other)->create();

    Transaction::factory()->for($user)->for($wallet1)->count(4)->create([
        'type' => TransactionType::Income->value,
    ]);
    Transaction::factory()->for($other)->for($wallet2)->count(3)->create([
        'type' => TransactionType::Income->value,
    ]);

    $this->actingAs($user);
    $this->assertSame(4, Transaction::count());
});

// ==================== الفلاتر ====================

test('index can filter by type', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    Transaction::factory()->for($user)->for($wallet)->create([
        'type'        => TransactionType::Income->value,
        'description' => 'راتب-فريد-للاختبار',
    ]);
    Transaction::factory()->for($user)->for($wallet)->create([
        'type'        => TransactionType::Expense->value,
        'description' => 'ايجار-فريد-للاختبار',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.index', ['type' => 'income']))
        ->assertOk()
        ->assertSee('راتب-فريد-للاختبار')
        ->assertDontSee('ايجار-فريد-للاختبار');
});

test('index can filter by date range', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    Transaction::factory()->for($user)->for($wallet)->create([
        'type'             => TransactionType::Income->value,
        'transaction_date' => '2025-01-15',
        'description'      => 'قديمة',
    ]);
    Transaction::factory()->for($user)->for($wallet)->create([
        'type'             => TransactionType::Income->value,
        'transaction_date' => now()->toDateString(),
        'description'      => 'حديثة',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to'   => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('حديثة')
        ->assertDontSee('قديمة');
});

test('index can search by description', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    Transaction::factory()->for($user)->for($wallet)->create([
        'type'        => TransactionType::Income->value,
        'description' => 'راتب أبريل',
    ]);
    Transaction::factory()->for($user)->for($wallet)->create([
        'type'        => TransactionType::Expense->value,
        'description' => 'إيجار شهري',
    ]);

    $this->actingAs($user)
        ->get(route('transactions.index', ['search' => 'راتب']))
        ->assertOk()
        ->assertSee('راتب أبريل')
        ->assertDontSee('إيجار شهري');
});

// ==================== الملخص المالي ====================

test('summary income and expense totals are correct', function () {
    $user   = User::factory()->create();
    $wallet = Wallet::factory()->for($user)->create();

    Transaction::factory()->for($user)->for($wallet)->create([
        'type'   => TransactionType::Income->value,
        'amount' => 3000,
    ]);
    Transaction::factory()->for($user)->for($wallet)->create([
        'type'   => TransactionType::Expense->value,
        'amount' => 1200,
    ]);

    // التحقق من الأرقام مباشرة عبر الـ service بـ request فارغ
    $this->actingAs($user);
    $service = app(\App\Modules\Transactions\Services\TransactionService::class);
    $summary = $service->getSummary(new \Illuminate\Http\Request());

    $this->assertEquals(3000, $summary['income']);
    $this->assertEquals(1200, $summary['expenses']);
    $this->assertEquals(1800, $summary['net']);
});
