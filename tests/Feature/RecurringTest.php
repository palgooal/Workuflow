<?php

use App\Models\RecurringTransaction;
use App\Models\User;

// ==================== الوصول ====================

test('guest cannot access recurring transactions', function () {
    $this->get(route('recurring.index'))->assertRedirect(route('login'));
});

test('user can view recurring transactions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('recurring.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a recurring transaction', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'type'        => 'expense',
            'amount'      => 1500,
            'description' => 'إيجار مكتب',
            'frequency'   => 'monthly',
            'start_date'  => now()->toDateString(),
            'currency'    => 'SAR',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('recurring_transactions', [
        'user_id'     => $user->id,
        'description' => 'إيجار مكتب',
        'amount'      => 1500,
        'frequency'   => 'monthly',
    ]);
});

test('recurring amount must be positive', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'type'        => 'expense',
            'amount'      => 0,
            'description' => 'اختبار',
            'frequency'   => 'monthly',
            'start_date'  => now()->toDateString(),
        ])
        ->assertSessionHasErrors('amount');
});

test('recurring frequency must be valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('recurring.store'), [
            'type'        => 'expense',
            'amount'      => 500,
            'description' => 'اختبار',
            'frequency'   => 'invalid',
            'start_date'  => now()->toDateString(),
        ])
        ->assertSessionHasErrors('frequency');
});

// ==================== التعديل ====================

test('user can update their recurring transaction', function () {
    $user      = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('recurring.update', $recurring->id), [
            'type'        => 'expense',
            'amount'      => 2500,
            'description' => 'إيجار محدّث',
            'frequency'   => 'monthly',
            'start_date'  => now()->toDateString(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('recurring_transactions', [
        'id'          => $recurring->id,
        'amount'      => 2500,
        'description' => 'إيجار محدّث',
    ]);
});

test('user cannot update another user recurring transaction', function () {
    $owner     = User::factory()->create();
    $other     = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($owner)->create();

    $this->actingAs($other)
        ->put(route('recurring.update', $recurring->id), [
            'type'        => 'expense',
            'amount'      => 1,
            'description' => 'اختراق',
            'frequency'   => 'monthly',
            'start_date'  => now()->toDateString(),
        ])
        ->assertNotFound();
});

// ==================== الحذف ====================

test('user can delete their recurring transaction', function () {
    $user      = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('recurring.destroy', $recurring->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('recurring_transactions', ['id' => $recurring->id]);
});

test('user cannot delete another user recurring transaction', function () {
    $owner     = User::factory()->create();
    $other     = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('recurring.destroy', $recurring->id))
        ->assertNotFound();
});

// ==================== تفعيل / إيقاف ====================

test('user can toggle recurring transaction status', function () {
    $user      = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($user)->create(['is_active' => true]);

    $this->actingAs($user)
        ->post(route('recurring.toggle', $recurring->id))
        ->assertRedirect();

    $this->assertDatabaseHas('recurring_transactions', [
        'id'        => $recurring->id,
        'is_active' => false,
    ]);
});

test('user cannot toggle another user recurring transaction', function () {
    $owner     = User::factory()->create();
    $other     = User::factory()->create();
    $recurring = RecurringTransaction::factory()->for($owner)->create();

    $this->actingAs($other)
        ->post(route('recurring.toggle', $recurring->id))
        ->assertNotFound();
});

// ==================== عزل البيانات ====================

test('user only sees their own recurring transactions', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    RecurringTransaction::factory()->for($user)->count(3)->create();
    RecurringTransaction::factory()->for($other)->count(2)->create();

    // BelongsToUser global scope يُرجع فقط بيانات المستخدم الحالي
    $this->actingAs($user);
    $this->assertSame(3, RecurringTransaction::count());
});
