<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('warranty_types')->insert([
            ['code' => 'CAR', 'name' => 'MOBIL / AUTOMOTIVE', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BUILDING', 'name' => 'BUILDING / BANGUNAN', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PPF', 'name' => 'PAINT PROTECTION FILM', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('warranties', function (Blueprint $table) {
            $table->integer('id_warranty_type')->nullable()->after('id_customer');
            $table->index('id_warranty_type', 'warranties_type_index');
            $table->foreign('id_warranty_type', 'warranties_type_foreign')->references('id')->on('warranty_types')->nullOnDelete();
        });
        DB::statement("ALTER TABLE warranties MODIFY status ENUM('Active', 'Claim', 'Expired', 'Void') NOT NULL DEFAULT 'Active'");

        Schema::create('warranty_vehicles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->unique();
            $table->string('no_polisi', 20)->nullable();
            $table->string('merk_mobil', 100)->nullable();
            $table->string('tipe_mobil', 100)->nullable();
            $table->string('warna_mobil', 50)->nullable();
            $table->string('tahun_mobil', 4)->nullable();
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
        });

        Schema::create('warranty_vehicle_items', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->index();
            $table->integer('id_product')->index();
            $table->string('posisi_kaca', 100);
            $table->date('tanggal_pasang');
            $table->date('tanggal_expired')->index();
            $table->enum('status', ['Active', 'Claim', 'Expired', 'Void'])->default('Active');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
            $table->foreign('id_product')->references('id_product')->on('products');
        });

        Schema::create('warranty_buildings', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->unique();
            $table->string('nama_bangunan', 150);
            $table->text('alamat');
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
        });

        Schema::create('warranty_building_items', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->index();
            $table->integer('id_product')->index();
            $table->string('area_pekerjaan', 150);
            $table->decimal('panjang', 10, 2);
            $table->decimal('lebar', 10, 2);
            $table->unsignedInteger('jumlah');
            $table->decimal('luas_per_item', 12, 2);
            $table->decimal('total_luas', 12, 2);
            $table->date('tanggal_pasang');
            $table->date('tanggal_expired')->index();
            $table->enum('status', ['Active', 'Claim', 'Expired', 'Void'])->default('Active');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
            $table->foreign('id_product')->references('id_product')->on('products');
        });

        Schema::create('warranty_ppfs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->unique();
            $table->string('no_polisi', 20)->nullable();
            $table->string('merk_mobil', 100)->nullable();
            $table->string('tipe_mobil', 100)->nullable();
            $table->string('warna_mobil', 50)->nullable();
            $table->string('tahun_mobil', 4)->nullable();
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
        });

        Schema::create('warranty_ppf_items', function (Blueprint $table) {
            $table->id();
            $table->integer('id_warranty')->index();
            $table->integer('id_product')->index();
            $table->string('area_pekerjaan', 150);
            $table->date('tanggal_pasang');
            $table->date('tanggal_expired')->index();
            $table->enum('status', ['Active', 'Claim', 'Expired', 'Void'])->default('Active');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->foreign('id_warranty')->references('id_warranty')->on('warranties')->cascadeOnDelete();
            $table->foreign('id_product')->references('id_product')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_ppf_items');
        Schema::dropIfExists('warranty_ppfs');
        Schema::dropIfExists('warranty_building_items');
        Schema::dropIfExists('warranty_buildings');
        Schema::dropIfExists('warranty_vehicle_items');
        Schema::dropIfExists('warranty_vehicles');
        Schema::table('warranties', function (Blueprint $table) { $table->dropForeign('warranties_type_foreign'); $table->dropIndex('warranties_type_index'); $table->dropColumn('id_warranty_type'); });
        DB::statement("ALTER TABLE warranties MODIFY status ENUM('Active', 'Void') NOT NULL DEFAULT 'Active'");
        Schema::dropIfExists('warranty_types');
    }
};
