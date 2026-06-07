<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Support\Enums\InvoiceStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

// ==================== الوصول ====================

test('guest cannot access invoices', function () {
    $this->get(route('invoices.index'))->assertRedirect(route('login'));
});

test('user can view invoices page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create an invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('invoices.store'), [
            'client_id'  => $client->id,
            'issue_date' => now()->toDateString(),
            'currency'   => 'SAR',
            'items'      => [
                ['description' => 'تصميم شعار', 'quantity' => 1, 'unit_price' => 2000],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'user_id'   => $user->id,
        'client_id' => $client->id,
        'status'    => InvoiceStatus::Draft->value,
    ]);
});

test('invoice requires at least one item', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('invoices.store'), [
            'client_id'  => $client->id,
            'issue_date' => now()->toDateString(),
            'currency'   => 'SAR',
            'items'      => [],
        ])
        ->assertSessionHasErrors('items');
});

test('invoice client must belong to user', function () {
    $user        = User::factory()->create();
    $otherUser   = User::factory()->create();
    $otherClient = Client::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->post(route('invoices.store'), [
            'client_id'  => $otherClient->id,
            'issue_date' => now()->toDateString(),
            'currency'   => 'SAR',
            'items'      => [
                ['description' => 'خدمة', 'quantity' => 1, 'unit_price' => 500],
            ],
        ])
        ->assertStatus(404);
});

// ==================== العرض ====================

test('user can view their invoice', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->withItem(1500)->create();

    $this->actingAs($user)
        ->get(route('invoices.show', $invoice->ulid))
        ->assertOk()
        ->assertSee($invoice->number);
});

test('user cannot view another user invoice', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $client  = Client::factory()->for($owner)->create();
    $invoice = Invoice::factory()->for($owner)->for($client)->create();

    $this->actingAs($other)
        ->get(route('invoices.show', $invoice->ulid))
        ->assertNotFound();
});

// ==================== تغيير الحالة ====================

test('user can mark invoice as sent', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create(['status' => InvoiceStatus::Draft]);

    $this->actingAs($user)
        ->post(route('invoices.mark-sent', $invoice->ulid))
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => InvoiceStatus::Sent->value,
    ]);
});

test('user can cancel an invoice', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create(['status' => InvoiceStatus::Sent]);

    $this->actingAs($user)
        ->post(route('invoices.cancel', $invoice->ulid))
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => InvoiceStatus::Cancelled->value,
    ]);
});

// ==================== تسجيل الدفع ====================

test('user can mark invoice as paid and transaction is created', function () {
    Queue::fake();

    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $wallet  = Wallet::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->withItem(3000)->create();

    $this->actingAs($user)
        ->post(route('invoices.mark-paid', $invoice->ulid), [
            'wallet_id' => $wallet->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'id'     => $invoice->id,
        'status' => InvoiceStatus::Paid->value,
    ]);

    $this->assertDatabaseHas('transactions', [
        'user_id'   => $user->id,
        'wallet_id' => $wallet->id,
        'type'      => 'income',
        'reference' => $invoice->fresh()->number,
    ]);
});

test('markPaid requires wallet_id', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    $this->actingAs($user)
        ->post(route('invoices.mark-paid', $invoice->ulid), [])
        ->assertSessionHasErrors('wallet_id');
});

test('marking already paid invoice is idempotent', function () {
    Queue::fake();

    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $wallet  = Wallet::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->paid()->create();

    $this->actingAs($user)
        ->post(route('invoices.mark-paid', $invoice->ulid), [
            'wallet_id' => $wallet->id,
        ])
        ->assertRedirect();

    // لم تُنشأ معاملة لأن الفاتورة كانت مدفوعة مسبقاً
    $this->assertDatabaseCount('transactions', 0);
});

// ==================== الإرسال بالبريد ====================

test('user can send invoice to client by email', function () {
    Mail::fake();

    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    $this->actingAs($user)
        ->post(route('invoices.send-client', $invoice->ulid), [
            'recipient_email' => 'client@example.com',
        ])
        ->assertRedirect();

    Mail::assertSent(\App\Mail\InvoiceMail::class);
});

test('send invoice requires valid email', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    $this->actingAs($user)
        ->post(route('invoices.send-client', $invoice->ulid), [
            'recipient_email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('recipient_email');
});

// ==================== الحذف ====================

test('user can delete their invoice', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create();

    $this->actingAs($user)
        ->delete(route('invoices.destroy', $invoice->ulid))
        ->assertRedirect();

    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('user cannot delete another user invoice', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $client  = Client::factory()->for($owner)->create();
    $invoice = Invoice::factory()->for($owner)->for($client)->create();

    $this->actingAs($other)
        ->delete(route('invoices.destroy', $invoice->ulid))
        ->assertNotFound();
});

// ==================== حساب المبالغ ====================

test('invoice total is calculated correctly with tax', function () {
    $user    = User::factory()->create();
    $client  = Client::factory()->for($user)->create();
    $invoice = Invoice::factory()->for($user)->for($client)->create([
        'tax_rate' => 15,
        'discount' => 0,
    ]);

    \App\Models\InvoiceItem::create([
        'invoice_id'  => $invoice->id,
        'description' => 'خدمة',
        'quantity'    => 2,
        'unit_price'  => 1000,
        'total'       => 2000,
        'sort_order'  => 0,
    ]);

    $invoice->load('items');
    $invoice->recalculate();
    $invoice->refresh();

    // subtotal=2000, tax=300 (15%), total=2300
    expect($invoice->subtotal)->toEqual('2000.00');
    expect($invoice->total)->toEqual('2300.00');
});
