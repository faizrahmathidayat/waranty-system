<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Warranty length moves from being a fixed property of the product
     * master data to a value entered per order item (order_details already
     * has warranty_months_snapshot for this). tanggal_expired_snapshot is
     * the new column: order_date + warranty_months_snapshot, computed and
     * stored when the order item is saved.
     */
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->date('tanggal_expired_snapshot')->nullable()->after('warranty_months_snapshot');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('masa_garansi_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedSmallInteger('masa_garansi_bulan')->nullable()->after('jenis');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('tanggal_expired_snapshot');
        });
    }
};
