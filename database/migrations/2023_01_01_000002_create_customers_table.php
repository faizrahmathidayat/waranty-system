<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Genesis table for the pre-existing legacy schema this app was built on.
     * No migration ever created it; vehicles/buildings/orders/invoices/warranties
     * all carry an unenforced integer id_customer column referencing it.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->integer('id_customer')->autoIncrement();
            $table->string('nama_customer', 150);
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->string('status', 20)->default('enabled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
