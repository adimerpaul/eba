<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('acopio_cosechas', function (Blueprint $table) {
            // Agregar nuevo estado RECHAZADO
            // Nota: En PostgreSQL necesitamos modificar el tipo ENUM si existe
            // Si no usa ENUM, este cambio no aplica
            
            // Agregar índice compuesto para optimizar consultas de acopios disponibles
            $table->index(['estado', 'fecha_cosecha', 'deleted_at'], 'idx_acopios_disponibles');
            
            // Agregar índice para búsquedas por producto
            $table->index(['producto_id', 'estado'], 'idx_acopios_producto_estado');
        });
        
        // Actualizar constraint del campo estado para incluir RECHAZADO
        DB::statement("ALTER TABLE traza.acopio_cosechas DROP CONSTRAINT IF EXISTS acopio_cosechas_estado_check");
        DB::statement("ALTER TABLE traza.acopio_cosechas ADD CONSTRAINT acopio_cosechas_estado_check CHECK (estado IN ('BUENO', 'EN_PROCESO', 'CANCELADO', 'RECHAZADO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acopio_cosechas', function (Blueprint $table) {
            $table->dropIndex('idx_acopios_disponibles');
            $table->dropIndex('idx_acopios_producto_estado');
        });
        
        // Restaurar constraint original
        DB::statement("ALTER TABLE traza.acopio_cosechas DROP CONSTRAINT IF EXISTS acopio_cosechas_estado_check");
        DB::statement("ALTER TABLE traza.acopio_cosechas ADD CONSTRAINT acopio_cosechas_estado_check CHECK (estado IN ('BUENO', 'EN_PROCESO', 'CANCELADO'))");
    }
};
