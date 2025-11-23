<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer que lote_id sea nullable
        DB::statement('ALTER TABLE traza.control_procesos ALTER COLUMN lote_id DROP NOT NULL');
    }

    public function down(): void
    {
        // Revertir a NOT NULL
        DB::statement('ALTER TABLE traza.control_procesos ALTER COLUMN lote_id SET NOT NULL');
    }
};
