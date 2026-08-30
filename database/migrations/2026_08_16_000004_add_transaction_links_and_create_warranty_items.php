<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            if (! Schema::hasColumn('warranties', 'id_order')) {
                $table->unsignedBigInteger('id_order')->nullable()->after('id_customer');
            }
            if (! Schema::hasColumn('warranties', 'id_invoice')) {
                $table->unsignedBigInteger('id_invoice')->nullable()->after('id_order');
            }
            if (! Schema::hasColumn('warranties', 'id_vehicle')) {
                $table->unsignedBigInteger('id_vehicle')->nullable()->after('id_invoice');
            }
            if (! Schema::hasColumn('warranties', 'id_building')) {
                $table->unsignedBigInteger('id_building')->nullable()->after('id_vehicle');
            }
        });

        // New nullable columns contain no legacy data, so these FKs are safe.
        Schema::table('warranties', function (Blueprint $table) {
            $table->index('id_order', 'warranties_order_index');
            $table->index('id_invoice', 'warranties_invoice_index');
            $table->index('id_vehicle', 'warranties_vehicle_index');
            $table->index('id_building', 'warranties_building_index');
            $table->foreign('id_order', 'warranties_order_foreign')->references('id_order')->on('orders')->nullOnDelete();
            $table->foreign('id_invoice', 'warranties_invoice_foreign')->references('id_invoice')->on('invoices')->nullOnDelete();
            $table->foreign('id_vehicle', 'warranties_vehicle_foreign')->references('id_vehicle')->on('vehicles')->nullOnDelete();
            $table->foreign('id_building', 'warranties_building_foreign')->references('id_building')->on('buildings')->nullOnDelete();
        });

        Schema::create('warranty_items', function (Blueprint $table) {
            $table->bigIncrements('id_warranty_item');
            // Existing warranties.id_warranty and products.id_product are signed INT.
            $table->integer('id_warranty');
            $table->unsignedBigInteger('id_order_detail')->nullable();
            $table->unsignedBigInteger('id_treatment')->nullable();
            $table->integer('id_product');
            $table->unsignedBigInteger('id_product_variant')->nullable();
            $table->string('item_type', 30);
            $table->string('area', 150)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 20)->default('unit');
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('luas_per_item', 12, 2)->nullable();
            $table->decimal('total_luas', 12, 2)->nullable();
            $table->date('tanggal_pasang');
            $table->date('tanggal_expired');
            $table->string('status', 20)->default('Active');
            $table->string('product_name_snapshot', 200);
            $table->string('variant_name_snapshot', 150)->nullable();
            $table->string('treatment_name_snapshot', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['id_warranty', 'status'], 'warranty_items_warranty_status_index');
            $table->index('id_order_detail', 'warranty_items_order_detail_index');
            $table->index(['id_product', 'tanggal_expired'], 'warranty_items_product_expired_index');
            $table->index(['id_treatment', 'tanggal_pasang'], 'warranty_items_treatment_install_index');
            $table->index(['item_type', 'status', 'tanggal_expired'], 'warranty_items_type_status_expired_index');
            $table->foreign('id_warranty', 'warranty_items_warranty_foreign')->references('id_warranty')->on('warranties')->cascadeOnDelete();
            $table->foreign('id_order_detail', 'warranty_items_order_detail_foreign')->references('id_order_detail')->on('order_details')->nullOnDelete();
            $table->foreign('id_treatment', 'warranty_items_treatment_foreign')->references('id_treatment')->on('treatments')->nullOnDelete();
            $table->foreign('id_product', 'warranty_items_product_foreign')->references('id_product')->on('products')->restrictOnDelete();
            $table->foreign('id_product_variant', 'warranty_items_variant_foreign')->references('id_product_variant')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_items');

        Schema::table('warranties', function (Blueprint $table) {
            $table->dropForeign('warranties_order_foreign');
            $table->dropForeign('warranties_invoice_foreign');
            $table->dropForeign('warranties_vehicle_foreign');
            $table->dropForeign('warranties_building_foreign');
            $table->dropIndex('warranties_order_index');
            $table->dropIndex('warranties_invoice_index');
            $table->dropIndex('warranties_vehicle_index');
            $table->dropIndex('warranties_building_index');
            $table->dropColumn(['id_order', 'id_invoice', 'id_vehicle', 'id_building']);
        });
    }
};
