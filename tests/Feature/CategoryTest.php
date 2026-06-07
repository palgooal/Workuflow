<?php

use App\Models\Category;
use App\Models\User;
use App\Support\Enums\TransactionType;

// ==================== الوصول ====================

test('guest cannot access categories', function () {
    $this->get(route('categories.index'))->assertRedirect(route('login'));
});

test('user can view categories page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a category', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'name'  => 'مواصلات',
            'type'  => TransactionType::Expense->value,
            'color' => '#EF4444',
            'icon'  => '🚗',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name'    => 'مواصلات',
        'type'    => 'expense',
    ]);
});

test('category name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'name'  => '',
            'type'  => 'expense',
            'color' => '#EF4444',
            'icon'  => '🚗',
        ])
        ->assertSessionHasErrors('name');
});

test('category type must be valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'name'  => 'فئة',
            'type'  => 'invalid-type',
            'color' => '#EF4444',
            'icon'  => '🚗',
        ])
        ->assertSessionHasErrors('type');
});

test('category color must be valid hex', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('categories.store'), [
            'name'  => 'فئة',
            'type'  => 'expense',
            'color' => 'not-a-color',
            'icon'  => '🚗',
        ])
        ->assertSessionHasErrors('color');
});

// ==================== التعديل ====================

test('user can update their category', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('categories.update', $category->id), [
            'name'  => 'اسم محدّث',
            'type'  => $category->type->value,
            'color' => '#10B981',
            'icon'  => '💡',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'id'   => $category->id,
        'name' => 'اسم محدّث',
    ]);
});

test('user cannot update another user category', function () {
    $owner    = User::factory()->create();
    $other    = User::factory()->create();
    $category = Category::factory()->for($owner)->create();

    $this->actingAs($other)
        ->put(route('categories.update', $category->id), [
            'name'  => 'اختراق',
            'type'  => 'expense',
            'color' => '#EF4444',
            'icon'  => '🚗',
        ])
        ->assertNotFound();
});

// ==================== الحذف ====================

test('user can delete their category', function () {
    $user     = User::factory()->create();
    $category = Category::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('categories.destroy', $category->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('user cannot delete another user category', function () {
    $owner    = User::factory()->create();
    $other    = User::factory()->create();
    $category = Category::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('categories.destroy', $category->id))
        ->assertNotFound();
});

// ==================== عزل البيانات ====================

test('user only sees their own categories', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    Category::factory()->for($user)->count(3)->create();
    Category::factory()->for($other)->count(2)->create();

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertOk();

    // تأكد أن الاستعلام يُرجع فقط فئات المستخدم (عبر BelongsToUser global scope)
    $this->assertSame(3, Category::count());
});
