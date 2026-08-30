<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->bigIncrements('id_product_type');
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('treatments', function (Blueprint $table) {
            $table->bigIncrements('id_treatment');
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'id_product_type')) {
                $table->unsignedBigInteger('id_product_type')->nullable()->after('id_product');
            }
            if (! Schema::hasColumn('products', 'kode_produk')) {
                $table->string('kode_produk', 50)->nullable()->unique()->after('id_product_type');
            }
            if (! Schema::hasColumn('products', 'harga_default')) {
                $table->decimal('harga_default', 15, 2)->nullable()->after('masa_garansi_bulan');
            }
            if (! Schema::hasColumn('products', 'is_warranty_eligible')) {
                $table->boolean('is_warranty_eligible')->default(true)->after('harga_default');
            }
            if (! Schema::hasColumn('products', 'created_by')) {
                $table->integer('created_by')->nullable()->after('is_warranty_eligible');
            }
            if (! Schema::hasColumn('products', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_by');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('id_product_type', 'products_product_type_foreign')
                ->references('id_product_type')->on('product_types')->nullOnDelete();
            $table->index(['id_product_type', 'status'], 'products_type_status_index');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id_product_variant');
            // Existing product foreign keys are signed INT in the multi-type migration.
            $table->integer('id_product');
            $table->string('code', 50)->nullable();
            $table->string('name', 100);
            $table->string('value', 50)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('harga_tambahan', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['id_product', 'is_active'], 'product_variants_product_active_index');
            $table->unique(['id_product', 'code'], 'product_variants_product_code_unique');
            $table->foreign('id_product', 'product_variants_product_foreign')
                ->references('id_product')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_product_type_foreign');
            $table->dropIndex('products_type_status_index');
            $columns = ['id_product_type', 'kode_produk', 'harga_default', 'is_warranty_eligible', 'created_by', 'updated_by'];
            $existing = array_filter($columns, fn (string $column) => Schema::hasColumn('products', $column));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });

        Schema::dropIfExists('treatments');
        Schema::dropIfExists('product_types');
    }
};
