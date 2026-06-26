<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_payment_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40)->default('togo');
            $table->string('order_id', 100)->nullable()->index();  // provider_order_id
            $table->json('payload')->nullable();                    // raw callback payload / query string
            $table->text('exception')->nullable();                  // exception message + trace
            $table->unsignedSmallInteger('retries')->default(0);
            $table->boolean('resolved')->default(false)->index();
            $table->timestamp('processed_at')->nullable();          // متى نجحت إعادة المعالجة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_payment_callbacks');
    }
};
