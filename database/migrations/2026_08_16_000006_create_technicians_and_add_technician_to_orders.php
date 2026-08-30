<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->bigIncrements('id_technician');
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name'], 'technicians_active_name_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id_technician')->nullable()->after('id_building');
            $table->index('id_technician', 'orders_technician_index');
            $table->foreign('id_technician', 'orders_technician_foreign')
                ->references('id_technician')->on('technicians')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_technician_foreign');
            $table->dropIndex('orders_technician_index');
            $table->dropColumn('id_technician');
        });
        Schema::dropIfExists('technicians');
    }
};
