<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Tag Assignments — ربط الوسوم بالعملاء (Many-to-Many Pivot)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_tag_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->references('id')->on('client_tags')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            // منع تكرار نفس الوسم على نفس العميل
            $table->unique(['client_id', 'tag_id'], 'client_tag_assignments_unique');
            $table->index('tag_id', 'cta_tag_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tag_assignments');
    }
};
