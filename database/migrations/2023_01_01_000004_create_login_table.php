<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Genesis table for the pre-existing legacy schema this app was built on.
     * No migration ever created it, even though App\Models\Login (the actual
     * auth-guard model, see config/auth.php) points at it. The older
     * 2023_04_08_054607_create_tb_user_table migration created a different,
     * superseded table missing the role/status columns Login needs.
     */
    public function up(): void
    {
        Schema::create('login', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('role', 30)->default('Staff');
            $table->string('status', 20)->default('Enabled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login');
    }
};
