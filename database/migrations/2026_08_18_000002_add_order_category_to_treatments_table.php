<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('treatments', 'order_category')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('order_category', 20)->default('AUTOMOTIVE')->index()->after('description');
            });
        }

        DB::table('treatments')
            ->where('code', 'KACA_FILM_GEDUNG')
            ->update(['order_category' => 'BUILDING']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('treatments', 'order_category')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->dropColumn('order_category');
            });
        }
    }
};
