<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->ulid('from_wallet_id');
            $table->ulid('to_wallet_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);      // رسوم التحويل
            $table->string('description')->nullable();
            $table->string('reference')->nullable();
            $table->date('transferred_at');
            $table->timestamps();

            $table->foreign('from_wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
            $table->foreign('to_wallet_id')->references('id')->on('wallets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};
