<?php

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use App\Modules\CRM\Actions\Client\DeleteUnlinkedClientsForUserAction;

/**
 * يغطي الوعد الوارد في docs/legal/Data-Deletion.md §3 و
 * docs/legal/Privacy-Policy.md §8:
 * - عميل CRM غير مرتبط بأي فاتورة/عرض سعر → يُحذف (Soft Delete).
 * - عميل مرتبط بفاتورة أو عرض سعر (حتى لو محذوفاً ناعماً) → يبقى دون حذف.
 */
test('deletes clients that are not linked to any invoice or quote', function () {
    $user = User::factory()->create();

    $unlinkedClient = Client::factory()->for($user)->create();

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    // fresh() يستخدم newQueryWithoutScopes() داخلياً، أي أنه يُزيل SoftDeletingScope
    // أيضاً — فيُعيد السجل المحذوف ناعماً بدل null، وهو غير موثوق للتحقق من الحذف
    // الناعم. الاستعلام الصريح عبر Client::query() (يطبّق النطاق الافتراضي الذي
    // يستثني المحذوفين) + assertSoftDeleted هو الفحص الصحيح هنا.
    $this->assertSoftDeleted('clients', [
        'id' => $unlinkedClient->id,
    ]);

    expect(Client::query()->find($unlinkedClient->id))->toBeNull()
        ->and(Client::onlyTrashed()->find($unlinkedClient->id))->not->toBeNull();
});

test('retains a client linked to an active invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Invoice::factory()->for($user)->create(['client_id' => $client->id]);

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    expect($client->fresh())->not->toBeNull()
        ->and($client->fresh()->trashed())->toBeFalse();
});

test('retains a client linked to a soft-deleted invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $invoice = Invoice::factory()->for($user)->create(['client_id' => $client->id]);
    $invoice->delete(); // Soft delete — الفاتورة لا تزال محفوظة ضمن مدة الاحتفاظ

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    expect($client->fresh())->not->toBeNull()
        ->and($client->fresh()->trashed())->toBeFalse();
});

test('retains a client linked to a quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    Quote::factory()->for($user)->create(['client_id' => $client->id]);

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    expect($client->fresh())->not->toBeNull()
        ->and($client->fresh()->trashed())->toBeFalse();
});

test('does not touch clients belonging to a different user', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    $otherUsersClient = Client::factory()->for($otherUser)->create();

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    expect($otherUsersClient->fresh())->not->toBeNull()
        ->and($otherUsersClient->fresh()->trashed())->toBeFalse();
});

test('logs deleted and retained counts to the activity log', function () {
    $user = User::factory()->create();

    $unlinked = Client::factory()->for($user)->create();
    $linked   = Client::factory()->for($user)->create();
    Invoice::factory()->for($user)->create(['client_id' => $linked->id]);

    $result = app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    expect($result['deleted_count'])->toBe(1)
        ->and($result['retained_count'])->toBe(1)
        ->and($result['deleted_client_ids'])->toContain($unlinked->public_id)
        ->and($result['retained_client_ids'])->toContain($linked->public_id);

    $this->assertDatabaseHas('activity_logs', [
        'user_id'    => $user->id,
        'event_type' => 'data_deletion.crm_clients_processed',
    ]);

    $log = ActivityLog::where('event_type', 'data_deletion.crm_clients_processed')
        ->where('user_id', $user->id)
        ->latest('id')
        ->first();

    expect($log->metadata['deleted_count'])->toBe(1)
        ->and($log->metadata['retained_count'])->toBe(1);
});

test('does not use a mass delete that would bypass client soft-delete/model events', function () {
    $user = User::factory()->create();

    $unlinked1 = Client::factory()->for($user)->create();
    $unlinked2 = Client::factory()->for($user)->create();

    app(DeleteUnlinkedClientsForUserAction::class)->execute($user, actorId: $user->id);

    // كلا العميلين محذوف ناعماً (deleted_at مضبوط)، وليس محذوفاً نهائياً (forceDelete)
    expect(Client::onlyTrashed()->find($unlinked1->id)?->deleted_at)->not->toBeNull()
        ->and(Client::onlyTrashed()->find($unlinked2->id)?->deleted_at)->not->toBeNull();
});
