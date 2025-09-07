<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('websites')) {
            Schema::create('websites', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                // Languages supported by this website (e.g., ["en","vi","th"]) 
                $table->json('languages')->nullable();
                // Structured content blocks, multilingual fields stored per-block
                $table->json('structure')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
