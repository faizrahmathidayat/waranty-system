<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id_order');
            $table->string('order_number', 50)->unique();
            // Kept without FK until the live customers.id_customer type is verified.
            $table->integer('id_customer');
            $table->string('order_type', 20);
            $table->unsignedBigInteger('id_vehicle')->nullable();
            $table->unsignedBigInteger('id_building')->nullable();
            $table->date('order_date');
            $table->string('status', 20)->default('DRAFT');
            $table->dateTime('service_completed_at')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['id_customer', 'order_date'], 'orders_customer_date_index');
            $table->index(['order_type', 'status', 'order_date'], 'orders_type_status_date_index');
            $table->index('id_vehicle', 'orders_vehicle_index');
            $table->index('id_building', 'orders_building_index');
            $table->foreign('id_vehicle', 'orders_vehicle_foreign')->references('id_vehicle')->on('vehicles')->nullOnDelete();
            $table->foreign('id_building', 'orders_building_foreign')->references('id_building')->on('buildings')->nullOnDelete();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id_order_detail');
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_treatment');
            // Existing products.id_product is signed INT according to the existing FK migration.
            $table->integer('id_product');
            $table->unsignedBigInteger('id_product_variant')->nullable();
            $table->string('area', 150)->nullable();
            $table->string('item_type', 30)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 20)->default('unit');
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('luas_per_item', 12, 2)->nullable();
            $table->decimal('total_luas', 12, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('warranty_eligible')->default(true);
            $table->unsignedSmallInteger('warranty_months_snapshot')->nullable();
            $table->string('service_status', 20)->default('PENDING');
            $table->dateTime('completed_at')->nullable();
            $table->integer('installer_id')->nullable();
            $table->string('product_name_snapshot', 200);
            $table->string('variant_name_snapshot', 150)->nullable();
            $table->string('treatment_name_snapshot', 150);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['id_order', 'service_status'], 'order_details_order_status_index');
            $table->index(['id_product', 'id_product_variant'], 'order_details_product_variant_index');
            $table->index(['id_treatment', 'completed_at'], 'order_details_treatment_completed_index');
            $table->index(['warranty_eligible', 'service_status'], 'order_details_warranty_status_index');
            $table->foreign('id_order', 'order_details_order_foreign')->references('id_order')->on('orders')->cascadeOnDelete();
            $table->foreign('id_treatment', 'order_details_treatment_foreign')->references('id_treatment')->on('treatments')->restrictOnDelete();
            $table->foreign('id_product', 'order_details_product_foreign')->references('id_product')->on('products')->restrictOnDelete();
            $table->foreign('id_product_variant', 'order_details_variant_foreign')->references('id_product_variant')->on('product_variants')->nullOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id_invoice');
            $table->string('invoice_number', 50)->unique();
            $table->unsignedBigInteger('id_order')->unique();
            // Kept without FK until the live customers.id_customer type is verified.
            $table->integer('id_customer');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('DRAFT');
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['id_customer', 'invoice_date'], 'invoices_customer_date_index');
            $table->index(['status', 'due_date'], 'invoices_status_due_index');
            $table->foreign('id_order', 'invoices_order_foreign')->references('id_order')->on('orders')->restrictOnDelete();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id_invoice_item');
            $table->unsignedBigInteger('id_invoice');
            $table->unsignedBigInteger('id_order_detail')->nullable();
            $table->string('product_name', 200);
            $table->string('variant_name', 150)->nullable();
            $table->string('treatment_name', 150);
            $table->string('area', 150)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 20)->default('unit');
            $table->decimal('panjang', 10, 2)->nullable();
            $table->decimal('lebar', 10, 2)->nullable();
            $table->decimal('total_luas', 12, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['id_invoice', 'id_order_detail'], 'invoice_items_invoice_order_detail_index');
            $table->foreign('id_invoice', 'invoice_items_invoice_foreign')->references('id_invoice')->on('invoices')->cascadeOnDelete();
            $table->foreign('id_order_detail', 'invoice_items_order_detail_foreign')->references('id_order_detail')->on('order_details')->nullOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id_payment');
            $table->unsignedBigInteger('id_invoice');
            $table->dateTime('payment_date');
            $table->string('payment_type', 20);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 20);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index(['id_invoice', 'payment_date'], 'payments_invoice_date_index');
            $table->index('payment_method', 'payments_method_index');
            $table->index('reference_number', 'payments_reference_index');
            $table->foreign('id_invoice', 'payments_invoice_foreign')->references('id_invoice')->on('invoices')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('orders');
    }
};
