<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Genesis table for the pre-existing legacy schema this app was built on.
     * No migration ever created it; 2026_08_12_000000_create_multi_type_warranty_structure
     * only ALTERs it (adding id_warranty_type, MODIFYing status to the 4-value
     * enum) and 2026_08_16_000004 adds id_order/id_invoice/id_vehicle/id_building.
     * id_warranty must stay a signed INT: warranty_vehicles/_buildings/_ppfs and
     * warranty_items all declare their id_warranty foreign key as plain integer().
     */
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->integer('id_warranty')->autoIncrement();
            $table->string('kode_warranty', 50)->unique();
            $table->string('pin_warranty', 10)->nullable();
            $table->string('qr_code', 255)->nullable();
            $table->integer('id_customer')->nullable();
            $table->integer('id_product')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('no_invoice', 50)->nullable();
            $table->string('no_polisi', 20)->nullable();
            $table->string('merk_mobil', 100)->nullable();
            $table->string('tipe_mobil', 100)->nullable();
            $table->string('warna_mobil', 50)->nullable();
            $table->string('tahun_mobil', 4)->nullable();
            $table->date('tanggal_pasang')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->string('installer', 100)->nullable();
            $table->enum('status', ['Active', 'Void'])->default('Active');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
