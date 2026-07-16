<?php

use App\Jobs\Export\ExportUserDataJob;
use App\Models\ActivityLog;
use App\Models\DataExportRequest;
use App\Models\User;
use App\Support\Enums\DataExportStatus;
use Illuminate\Support\Facades\Queue;

// ==================== الوصول ====================

test('guest cannot request a data export', function () {
    $this->post(route('data-export.store'))->assertRedirect(route('login'));
});

// ==================== الطلب ====================

test('authenticated user can request a data export', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('data-export.store'))
        ->assertRedirect();

    $this->assertDatabaseHas('data_export_requests', [
        'user_id' => $user->id,
        'status'  => DataExportStatus::Pending->value,
    ]);

    Queue::assertPushed(ExportUserDataJob::class);
});

test('requesting an export dispatches the job and logs activity', function () {
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('data-export.store'));

    $request = DataExportRequest::where('user_id', $user->id)->first();

    Queue::assertPushed(ExportUserDataJob::class);
    $this->assertDatabaseHas('activity_logs', [
        'event_type'  => 'export.requested',
        'entity_type' => DataExportRequest::class,
        'entity_id'   => $request->id,
    ]);
});

// ==================== منع أكثر من طلب نشط ====================

test('user cannot request a new export while one is active', function () {
    Queue::fake();

    $user = User::factory()->create();
    DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Pending,
        'requested_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('data-export.store'))
        ->assertSessionHas('error');

    $this->assertDatabaseCount('data_export_requests', 1);
    Queue::assertNotPushed(ExportUserDataJob::class);
});

test('processing status also blocks a new request', function () {
    Queue::fake();

    $user = User::factory()->create();
    DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Processing,
        'requested_at' => now(),
    ]);

    $this->actingAs($user)->post(route('data-export.store'));

    $this->assertDatabaseCount('data_export_requests', 1);
});

// ==================== Rate limit (طلب واحد كل 24 ساعة) ====================

test('user cannot request a second export within the rate limit window', function () {
    Queue::fake();

    $user = User::factory()->create();

    // الطلب الأول يكتمل (وليس نشطاً) حتى لا يُحجب بقاعدة "الطلب النشط"
    DataExportRequest::create([
        'user_id'      => $user->id,
        'status'       => DataExportStatus::Completed,
        'requested_at' => now()->subHour(),
        'completed_at' => now()->subHour(),
        'expires_at'   => now()->addDay(),
        'file_path'    => 'user-data-exports/fake.zip',
    ]);

    $this->actingAs($user)->post(route('data-export.store'));
    $this->assertDatabaseCount('data_export_requests', 2);

    // الطلب الثاني يجب أن يُحجب بواسطة rate limiter
    $this->actingAs($user)
        ->post(route('data-export.store'))
        ->assertSessionHas('error');

    $this->assertDatabaseCount('data_export_requests', 2);
});

// ==================== عزل بين المستخدمين ====================

test('one user active request does not block another user', function () {
    Queue::fake();

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    DataExportRequest::create([
        'user_id'      => $userA->id,
        'status'       => DataExportStatus::Pending,
        'requested_at' => now(),
    ]);

    $this->actingAs($userB)
        ->post(route('data-export.store'))
        ->assertRedirect();

    $this->assertDatabaseHas('data_export_requests', ['user_id' => $userB->id]);
});
