<?php

use App\Models\TeamMember;
use App\Models\User;

// ==================== الوصول ====================

test('guest cannot access team members', function () {
    $this->get(route('team.index'))->assertRedirect(route('login'));
});

test('user can view team members page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('team.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a team member', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('team.store'), [
            'name'         => 'أحمد محمد',
            'type'         => 'freelancer',
            'specialty'    => 'تطوير ويب',
            'default_rate' => 150,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_members', [
        'user_id' => $user->id,
        'name'    => 'أحمد محمد',
        'type'    => 'freelancer',
    ]);
});

test('team member name is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('team.store'), [
            'name' => '',
            'type' => 'employee',
        ])
        ->assertSessionHasErrors('name');
});

test('team member type must be valid', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('team.store'), [
            'name' => 'اختبار',
            'type' => 'invalid',
        ])
        ->assertSessionHasErrors('type');
});

// ==================== التعديل ====================

test('user can update their team member', function () {
    $user   = User::factory()->create();
    $member = TeamMember::factory()->for($user)->create();

    $this->actingAs($user)
        ->put(route('team.update', $member), [
            'name' => 'اسم محدّث',
            'type' => $member->type,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('team_members', [
        'id'   => $member->id,
        'name' => 'اسم محدّث',
    ]);
});

test('user cannot update another user team member', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $member = TeamMember::factory()->for($owner)->create();

    $this->actingAs($other)
        ->put(route('team.update', $member), [
            'name' => 'اختراق',
            'type' => 'freelancer',
        ])
        ->assertNotFound();
});

// ==================== الحذف ====================

test('user can delete their team member', function () {
    $user   = User::factory()->create();
    $member = TeamMember::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('team.destroy', $member))
        ->assertRedirect();

    $this->assertSoftDeleted('team_members', ['id' => $member->id]);
});

test('user cannot delete another user team member', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $member = TeamMember::factory()->for($owner)->create();

    $this->actingAs($other)
        ->delete(route('team.destroy', $member))
        ->assertNotFound();
});

// ==================== عزل البيانات ====================

test('user only sees their own team members', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    TeamMember::factory()->for($user)->count(3)->create();
    TeamMember::factory()->for($other)->count(2)->create();

    $this->actingAs($user);
    $this->assertSame(3, TeamMember::count());
});
