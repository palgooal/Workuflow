<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 80);          // login, logout, project.created, payment.succeeded ...
            $table->string('entity_type', 80)->nullable(); // App\Models\Project, App\Models\Invoice ...
            $table->string('entity_id', 36)->nullable();   // ULID or int ID
            $table->json('metadata')->nullable();           // extra context
            $table->string('ip_address', 45)->nullable();  // IPv4 or IPv6
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
