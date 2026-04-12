<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('author')->nullable();
            $table->string('image_url')->nullable();
            $table->string('source_url');
            $table->string('language', 10)->default('id');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_translated')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['news_source_id', 'external_id']);
            $table->index('published_at');
            $table->index('is_approved');
            $table->index('is_featured');
            $table->index('language');
            $table->index('synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
