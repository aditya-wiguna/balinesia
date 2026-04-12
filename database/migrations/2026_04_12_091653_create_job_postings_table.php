<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable();
            $table->string('company_name');
            $table->string('job_title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('location');
            $table->string('employment_type')->nullable();
            $table->string('category')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('source_url');
            $table->string('source_name')->default('LokerBali');
            $table->boolean('is_remote')->default(false);
            $table->timestamp('posted_date')->nullable();
            $table->timestamp('expires_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['source_name', 'external_id']);
            $table->index('is_active');
            $table->index('is_approved');
            $table->index('location');
            $table->index('employment_type');
            $table->index('posted_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
