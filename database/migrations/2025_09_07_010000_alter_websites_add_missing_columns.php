<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('websites')) {
            Schema::table('websites', function (Blueprint $table) {
                if (! Schema::hasColumn('websites', 'languages')) {
                    $table->json('languages')->nullable()->after('slug');
                }
                if (! Schema::hasColumn('websites', 'structure')) {
                    $table->json('structure')->nullable()->after('languages');
                }
                if (! Schema::hasColumn('websites', 'is_published')) {
                    $table->boolean('is_published')->default(false)->after('structure');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('websites')) {
            Schema::table('websites', function (Blueprint $table) {
                if (Schema::hasColumn('websites', 'is_published')) {
                    $table->dropColumn('is_published');
                }
                if (Schema::hasColumn('websites', 'structure')) {
                    $table->dropColumn('structure');
                }
                if (Schema::hasColumn('websites', 'languages')) {
                    $table->dropColumn('languages');
                }
            });
        }
    }
};

