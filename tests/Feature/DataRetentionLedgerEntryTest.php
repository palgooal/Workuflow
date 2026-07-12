<?php

use App\Models\DataRetentionLedgerEntry;
use App\Models\User;

test('recordClosure sets purge_due_at to one year from now by default', function () {
    $user = User::factory()->create();

    $entry = DataRetentionLedgerEntry::recordClosure($user, triggeredByAdminId: null);

    expect($entry->status)->toBe('pending')
        ->and($entry->legal_hold)->toBeFalse()
        ->and($entry->closed_at->isToday())->toBeTrue()
        // من closed_at إلى purge_due_at (وليس العكس) — يُعبِّر عن المعنى التجاري المقصود:
        // عدد الأيام من إغلاق الحساب حتى موعد استحقاق التطهير. الاتجاه المعاكس كان يُرجع
        // فرقاً سالباً (-365) في هذه البيئة لأن purge_due_at لاحق زمنياً لـ closed_at.
        ->and($entry->closed_at->diffInDays($entry->purge_due_at))->toBeGreaterThanOrEqual(364)
        ->and($entry->closed_at->diffInDays($entry->purge_due_at))->toBeLessThanOrEqual(366);
});

test('dueForPurge scope only returns pending non-legal-hold entries past their due date', function () {
    $user = User::factory()->create();

    $due = DataRetentionLedgerEntry::create([
        'user_id'             => $user->id,
        'user_email_snapshot' => $user->email,
        'closed_at'           => now()->subYears(2),
        'purge_due_at'        => now()->subDay(),
        'status'              => 'pending',
        'legal_hold'          => false,
    ]);

    $notYetDue = DataRetentionLedgerEntry::create([
        'user_id'             => $user->id,
        'user_email_snapshot' => $user->email,
        'closed_at'           => now(),
        'purge_due_at'        => now()->addMonths(6),
        'status'              => 'pending',
        'legal_hold'          => false,
    ]);

    $onLegalHold = DataRetentionLedgerEntry::create([
        'user_id'             => $user->id,
        'user_email_snapshot' => $user->email,
        'closed_at'           => now()->subYears(2),
        'purge_due_at'        => now()->subDay(),
        'status'              => 'pending',
        'legal_hold'          => true,
        'legal_hold_reason'   => 'نزاع قانوني قائم',
    ]);

    $alreadyPurged = DataRetentionLedgerEntry::create([
        'user_id'             => $user->id,
        'user_email_snapshot' => $user->email,
        'closed_at'           => now()->subYears(2),
        'purge_due_at'        => now()->subDay(),
        'status'              => 'purged',
        'legal_hold'          => false,
        'purged_at'           => now(),
    ]);

    $results = DataRetentionLedgerEntry::dueForPurge()->pluck('id');

    expect($results)->toContain($due->id)
        ->and($results)->not->toContain($notYetDue->id)
        ->and($results)->not->toContain($onLegalHold->id)
        ->and($results)->not->toContain($alreadyPurged->id);
});
