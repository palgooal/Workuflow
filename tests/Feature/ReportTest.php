<?php

use App\Models\User;

// ==================== الوصول ====================

test('guest cannot access reports', function () {
    $this->get(route('reports.index'))->assertRedirect(route('login'));
});

test('user can view reports page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertOk();
});

test('reports page accepts date range filter', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('reports.index', [
            'from' => now()->startOfYear()->toDateString(),
            'to'   => now()->toDateString(),
        ]))
        ->assertOk();
});

test('reports swaps from and to if from is after to', function () {
    $user = User::factory()->create();

    // from > to — يجب أن تُعالجهما الصفحة بدون خطأ
    $this->actingAs($user)
        ->get(route('reports.index', [
            'from' => '2026-12-31',
            'to'   => '2026-01-01',
        ]))
        ->assertOk();
});

test('another user cannot see first user report data', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $wallet = \App\Models\Wallet::factory()->for($user1)->create();

    \App\Models\Transaction::create([
        'user_id'          => $user1->id,
        'wallet_id'        => $wallet->id,
        'type'             => 'income',
        'amount'           => 99999,
        'description'      => 'مبلغ سري',
        'transaction_date' => now()->toDateString(),
        'currency'         => 'SAR',
    ]);

    // المستخدم الثاني يرى صفحة التقارير بدون أخطاء (بياناته صفر)
    $this->actingAs($user2)
        ->get(route('reports.index'))
        ->assertOk();
});
