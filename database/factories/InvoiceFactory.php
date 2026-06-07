<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Support\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'client_id'  => Client::factory(),
            'project_id' => null,
            'status'     => InvoiceStatus::Draft,
            'issue_date' => now()->toDateString(),
            'due_date'   => now()->addDays(30)->toDateString(),
            'tax_rate'   => 0,
            'discount'   => 0,
            'currency'   => 'SAR',
            'subtotal'   => 1000,
            'tax_amount' => 0,
            'total'      => 1000,
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => InvoiceStatus::Sent, 'sent_at' => now()]);
    }

    public function paid(): static
    {
        return $this->state(['status' => InvoiceStatus::Paid, 'paid_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => InvoiceStatus::Cancelled]);
    }

    public function withItem(float $unitPrice = 1000, float $qty = 1): static
    {
        return $this->afterCreating(function ($invoice) use ($unitPrice, $qty) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => 'خدمة احترافية',
                'quantity'    => $qty,
                'unit_price'  => $unitPrice,
                'total'       => $unitPrice * $qty,
                'sort_order'  => 0,
            ]);
            $invoice->load('items');
            $invoice->recalculate();
        });
    }
}
