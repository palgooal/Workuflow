<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\QuoteItem;
use App\Models\User;
use App\Support\Enums\QuoteStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'client_id'  => Client::factory(),
            'project_id' => null,
            'status'     => QuoteStatus::Draft,
            'issue_date' => now()->toDateString(),
            'valid_until'=> now()->addDays(30)->toDateString(),
            'currency'   => 'SAR',
            'tax_rate'   => 0,
            'discount'   => 0,
            'subtotal'   => 1000,
            'tax_amount' => 0,
            'total'      => 1000,
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => QuoteStatus::Sent, 'sent_at' => now()]);
    }

    public function accepted(): static
    {
        return $this->state(['status' => QuoteStatus::Accepted, 'accepted_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => QuoteStatus::Rejected, 'rejected_at' => now()]);
    }

    public function withItem(float $unitPrice = 1000, float $qty = 1): static
    {
        return $this->afterCreating(function ($quote) use ($unitPrice, $qty) {
            QuoteItem::create([
                'quote_id'    => $quote->id,
                'description' => 'خدمة احترافية',
                'quantity'    => $qty,
                'unit_price'  => $unitPrice,
                'total'       => $unitPrice * $qty,
                'sort_order'  => 0,
            ]);
            $quote->load('items');
            $quote->recalculate();
        });
    }
}
