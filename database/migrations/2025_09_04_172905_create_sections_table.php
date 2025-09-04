<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('language', 8)->default('en');
            $table->enum('type', ['h1', 'h2', 'tags', 'content']);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->json('components')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->unique(['website_id', 'slug', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
