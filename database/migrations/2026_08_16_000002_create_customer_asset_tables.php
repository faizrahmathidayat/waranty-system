<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id_vehicle');
            // Kept without FK until the live customers.id_customer type is verified.
            $table->integer('id_customer');
            $table->string('no_polisi', 20)->nullable();
            $table->string('merk', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('warna', 50)->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('status', 20)->default('active');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['id_customer', 'status'], 'vehicles_customer_status_index');
            $table->index('no_polisi', 'vehicles_plate_index');
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->bigIncrements('id_building');
            // Kept without FK until the live customers.id_customer type is verified.
            $table->integer('id_customer');
            $table->string('nama_bangunan', 150);
            $table->text('alamat');
            $table->string('status', 20)->default('active');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index(['id_customer', 'status'], 'buildings_customer_status_index');
            $table->index('nama_bangunan', 'buildings_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('vehicles');
    }
};
