<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing Products keep today's behaviour: the order item asks for an Area.
        Schema::table('product_types', function (Blueprint $table) {
            $table->boolean('is_area_required')->default(true)->after('order_category');
        });
    }

    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn('is_area_required');
        });
    }
};
