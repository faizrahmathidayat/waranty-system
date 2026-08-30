<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_types', 'order_category')) {
            Schema::table('product_types', function (Blueprint $table) {
                $table->string('order_category', 20)->default('AUTOMOTIVE')->index()->after('description');
            });
        }

        // Preserve the existing Building catalogue when upgrading old data.
        DB::table('product_types')
            ->where('code', 'KACA_FILM_GEDUNG')
            ->update(['order_category' => 'BUILDING']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_types', 'order_category')) {
            Schema::table('product_types', function (Blueprint $table) {
                $table->dropColumn('order_category');
            });
        }
    }
};
