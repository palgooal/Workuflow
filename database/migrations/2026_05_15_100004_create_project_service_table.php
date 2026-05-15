<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_service', function (Blueprint $table) {
            $table->id();
            $table->string('project_id'); // ULID
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('type', ['income', 'expense'])->default('income');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->index(['project_id', 'service_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_service');
    }
};
