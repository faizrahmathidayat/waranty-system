<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The Treatment module was retired: new order items no longer carry a treatment.
// Existing rows keep their treatment data, so the columns become nullable rather than dropped.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE order_details MODIFY id_treatment BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE order_details MODIFY treatment_name_snapshot VARCHAR(150) NULL');
        DB::statement('ALTER TABLE invoice_items MODIFY treatment_name VARCHAR(150) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE order_details SET treatment_name_snapshot = '' WHERE treatment_name_snapshot IS NULL");
        DB::statement("UPDATE invoice_items SET treatment_name = '' WHERE treatment_name IS NULL");
        DB::statement('ALTER TABLE order_details MODIFY treatment_name_snapshot VARCHAR(150) NOT NULL');
        DB::statement('ALTER TABLE invoice_items MODIFY treatment_name VARCHAR(150) NOT NULL');
        // id_treatment is left nullable on rollback: rows created without a treatment have no value to restore.
    }
};
