<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'status')) $table->string('status', 20)->default('ACTIVE')->after('notes');
            if (!Schema::hasColumn('payments', 'voided_at')) $table->dateTime('voided_at')->nullable()->after('status');
            if (!Schema::hasColumn('payments', 'voided_by')) $table->integer('voided_by')->nullable()->after('voided_at');
            if (!Schema::hasColumn('payments', 'void_reason')) $table->text('void_reason')->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = array_filter(['void_reason', 'voided_by', 'voided_at', 'status'], fn ($column) => Schema::hasColumn('payments', $column));
            if ($columns) $table->dropColumn($columns);
        });
    }
};
