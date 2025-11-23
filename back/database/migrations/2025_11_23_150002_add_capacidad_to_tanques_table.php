<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración para agregar campos de capacidad a tabla tanques.
     * Permite control de ocupación y disponibilidad de tanques de decantación.
     */
    public function up(): void
    {
        Schema::table('tanques', function (Blueprint $table) {
            // Capacidades del tanque
            $table->decimal('capacidad_litros', 10, 2)->nullable()->after('planta_id')
                ->comment('Capacidad máxima del tanque en litros');
            
            $table->decimal('capacidad_kg', 10, 2)->nullable()->after('capacidad_litros')
                ->comment('Capacidad máxima del tanque en kilogramos (aprox 1.42 kg/litro para miel)');
            
            // Estado operativo
            $table->enum('estado_operativo', ['OPERATIVO', 'MANTENIMIENTO', 'FUERA_SERVICIO'])
                ->default('OPERATIVO')
                ->after('capacidad_kg')
                ->comment('Estado actual del tanque para control operativo');
            
            // Información adicional
            $table->text('descripcion')->nullable()->after('estado_operativo')
                ->comment('Descripción o características adicionales del tanque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanques', function (Blueprint $table) {
            $table->dropColumn([
                'capacidad_litros',
                'capacidad_kg',
                'estado_operativo',
                'descripcion'
            ]);
        });
    }
};
