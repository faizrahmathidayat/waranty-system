<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * customers/products only exist as of the 2023_01_01_* genesis migrations,
     * added later than these tables. Several FKs were deliberately left off
     * pending that ("Kept without FK until the live customers.id_customer
     * type is verified") -- now that the base tables exist and are confirmed
     * to hold no orphaned rows, wire the constraints up for real.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('id_customer', 'vehicles_customer_foreign')
                ->references('id_customer')->on('customers')->restrictOnDelete();
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->foreign('id_customer', 'buildings_customer_foreign')
                ->references('id_customer')->on('customers')->restrictOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('id_customer', 'orders_customer_foreign')
                ->references('id_customer')->on('customers')->restrictOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('id_customer', 'invoices_customer_foreign')
                ->references('id_customer')->on('customers')->restrictOnDelete();
        });

        Schema::table('warranties', function (Blueprint $table) {
            $table->foreign('id_customer', 'warranties_customer_foreign')
                ->references('id_customer')->on('customers')->restrictOnDelete();
            $table->foreign('id_product', 'warranties_product_foreign')
                ->references('id_product')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            $table->dropForeign('warranties_customer_foreign');
            $table->dropForeign('warranties_product_foreign');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_customer_foreign');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_customer_foreign');
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->dropForeign('buildings_customer_foreign');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign('vehicles_customer_foreign');
        });
    }
};
