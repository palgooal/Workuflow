<?php

use App\Models\Client;
use App\Models\Quote;
use App\Models\User;
use App\Support\Enums\InvoiceStatus;
use App\Support\Enums\QuoteStatus;

// ==================== الوصول ====================

test('guest cannot access quotes', function () {
    $this->get(route('quotes.index'))->assertRedirect(route('login'));
});

test('user can view quotes page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('quotes.index'))
        ->assertOk();
});

// ==================== الإنشاء ====================

test('user can create a quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('quotes.store'), [
            'client_id'  => $client->id,
            'issue_date' => now()->toDateString(),
            'currency'   => 'SAR',
            'items'      => [
                ['description' => 'تصميم هوية', 'quantity' => 1, 'unit_price' => 3000],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('quotes', [
        'user_id'   => $user->id,
        'client_id' => $client->id,
        'status'    => QuoteStatus::Draft->value,
    ]);
});

test('quote requires at least one item', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('quotes.store'), [
            'client_id'  => $client->id,
            'issue_date' => now()->toDateString(),
            'currency'   => 'SAR',
            'items'      => [],
        ])
        ->assertSessionHasErrors('items');
});

test('quote client must belong to user', function () {
    $user        = User::factory()->create();
    $otherUser   = User::factory()->create();
    $otherClient = Client::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->post(route('quotes.store'), [
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

test('user can view their quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->withItem(2000)->create();

    $this->actingAs($user)
        ->get(route('quotes.show', $quote->ulid))
        ->assertOk()
        ->assertSee($quote->number);
});

test('user cannot view another user quote', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $quote  = Quote::factory()->for($owner)->for($client)->create();

    $this->actingAs($other)
        ->get(route('quotes.show', $quote->ulid))
        ->assertNotFound();
});

// ==================== تغيير الحالة ====================

test('user can mark quote as sent', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->create(['status' => QuoteStatus::Draft]);

    $this->actingAs($user)
        ->post(route('quotes.mark-sent', $quote->ulid))
        ->assertRedirect();

    $this->assertDatabaseHas('quotes', [
        'id'     => $quote->id,
        'status' => QuoteStatus::Sent->value,
    ]);
});

test('cannot mark accepted quote as sent', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->accepted()->create();

    $this->actingAs($user)
        ->post(route('quotes.mark-sent', $quote->ulid))
        ->assertStatus(422);
});

// ==================== التحويل إلى فاتورة ====================

test('user can convert accepted quote to invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->accepted()->withItem(5000)->create();

    $this->actingAs($user)
        ->post(route('quotes.convert', $quote->ulid))
        ->assertRedirect();

    // تأكد أن الفاتورة أُنشئت
    $this->assertDatabaseHas('invoices', [
        'user_id'   => $user->id,
        'client_id' => $client->id,
        'status'    => InvoiceStatus::Draft->value,
    ]);

    // تأكد أن حالة العرض أصبحت "محوّل"
    $this->assertDatabaseHas('quotes', [
        'id'     => $quote->id,
        'status' => QuoteStatus::Converted->value,
    ]);
});

test('cannot convert draft quote to invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->create(['status' => QuoteStatus::Draft]);

    $this->actingAs($user)
        ->post(route('quotes.convert', $quote->ulid))
        ->assertStatus(422);
});

test('cannot convert rejected quote to invoice', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->rejected()->create();

    $this->actingAs($user)
        ->post(route('quotes.convert', $quote->ulid))
        ->assertStatus(422);
});

// ==================== بوابة العميل ====================

test('client can view quote via portal token', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->sent()->create();

    $this->get(route('quotes.portal', $quote->token))
        ->assertOk();
});

test('viewing sent quote via portal marks it as viewed', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->sent()->create();

    $this->get(route('quotes.portal', $quote->token));

    $this->assertDatabaseHas('quotes', [
        'id'     => $quote->id,
        'status' => QuoteStatus::Viewed->value,
    ]);
});

test('client can accept sent quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->sent()->create();

    $this->post(route('quotes.accept', $quote->token))
        ->assertRedirect();

    $this->assertDatabaseHas('quotes', [
        'id'     => $quote->id,
        'status' => QuoteStatus::Accepted->value,
    ]);
});

test('client can reject sent quote with reason', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->sent()->create();

    $this->post(route('quotes.reject', $quote->token), [
            'rejection_reason' => 'السعر مرتفع',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('quotes', [
        'id'              => $quote->id,
        'status'          => QuoteStatus::Rejected->value,
        'rejection_reason'=> 'السعر مرتفع',
    ]);
});

test('client cannot accept already accepted quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->accepted()->create();

    $this->post(route('quotes.accept', $quote->token))
        ->assertStatus(422);
});

test('client cannot accept expired quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->sent()->create([
        'valid_until' => now()->subDay()->toDateString(),
    ]);

    $this->post(route('quotes.accept', $quote->token))
        ->assertStatus(422);
});

// ==================== الحذف ====================

test('user can delete their quote', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->create();

    $this->actingAs($user)
        ->delete(route('quotes.destroy', $quote->ulid))
        ->assertRedirect();

    $this->assertSoftDeleted('quotes', ['id' => $quote->id]);
});

test('user cannot delete another user quote', function () {
    $owner  = User::factory()->create();
    $other  = User::factory()->create();
    $client = Client::factory()->for($owner)->create();
    $quote  = Quote::factory()->for($owner)->for($client)->create();

    $this->actingAs($other)
        ->delete(route('quotes.destroy', $quote->ulid))
        ->assertNotFound();
});

// ==================== حساب المبالغ ====================

test('quote total is calculated correctly with tax', function () {
    $user   = User::factory()->create();
    $client = Client::factory()->for($user)->create();
    $quote  = Quote::factory()->for($user)->for($client)->create([
        'tax_rate' => 15,
        'discount' => 0,
    ]);

    \App\Models\QuoteItem::create([
        'quote_id'    => $quote->id,
        'description' => 'استشارة',
        'quantity'    => 2,
        'unit_price'  => 1000,
        'total'       => 2000,
        'sort_order'  => 0,
    ]);

    $quote->load('items');
    $quote->recalculate();
    $quote->refresh();

    // subtotal=2000, tax=300 (15%), total=2300
    expect($quote->subtotal)->toEqual('2000.00');
    expect($quote->total)->toEqual('2300.00');
});
