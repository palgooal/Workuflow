<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();

            $table->ulid('page_id');
            $table->foreign('page_id')->references('id')->on('pages')->cascadeOnDelete();

            // النسخة المنشورة وقت أخذ اللقطة (snapshot) — عادة تطابق document_version وقتها
            $table->string('version', 20)->nullable();

            $table->string('title');
            $table->longText('content');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_note')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->index('page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
