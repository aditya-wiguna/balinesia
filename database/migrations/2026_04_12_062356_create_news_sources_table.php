<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('api_endpoint')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('language', 10)->default('id');
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['url']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
