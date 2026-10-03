<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->bigIncrements('id_bank_account');
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_holder', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'bank_name'], 'bank_accounts_active_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
