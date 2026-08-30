<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Genesis table for the pre-existing legacy schema this app was built on.
     * No migration ever created it; 2026_08_16_000001_create_product_catalog_tables
     * only ALTERs it (adding id_product_type, kode_produk, harga_default,
     * is_warranty_eligible, created_by, updated_by after this base shape).
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->integer('id_product')->autoIncrement();
            $table->string('nama_produk', 150);
            $table->string('brand', 100)->nullable();
            $table->string('jenis', 100)->nullable();
            $table->unsignedSmallInteger('masa_garansi_bulan')->nullable();
            $table->string('status', 20)->default('enabled');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
