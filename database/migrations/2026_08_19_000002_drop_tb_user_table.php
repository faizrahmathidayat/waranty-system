<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * tb_user (2023_04_08_054607_create_tb_user_table) was superseded by the
     * "login" table (App\Models\Login, see config/auth.php) early on and has
     * been unused ever since -- it's missing the role/status columns Login
     * needs and nothing in the app queries it.
     */
    public function up(): void
    {
        Schema::dropIfExists('tb_user');
    }

    public function down(): void
    {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }
};
