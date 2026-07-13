<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('title');
            $table->string('slug')->unique();

            // VARCHAR + PHP Enum (App\Support\Enums\PageType) — بدون ENUM في قاعدة البيانات
            $table->string('page_type', 20)->default('general');

            $table->longText('content');
            $table->text('excerpt')->nullable();

            // VARCHAR + PHP Enum (App\Support\Enums\PageStatus) — بدون ENUM في قاعدة البيانات
            $table->string('status', 20)->default('draft');

            $table->boolean('show_in_footer')->default(false);

            // VARCHAR + PHP Enum (App\Support\Enums\PageFooterGroup) — بدون ENUM في قاعدة البيانات
            $table->string('footer_group', 20)->default('none');

            $table->string('footer_label')->nullable();
            $table->integer('sort_order')->default(0);

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_description')->nullable();
            $table->string('document_version', 20)->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'show_in_footer', 'footer_group', 'sort_order'], 'pages_footer_listing_idx');
            $table->index('page_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
