<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_procesos', function (Blueprint $table) {
            // Agregar solo campos que no existen
            if (!Schema::hasColumn('control_procesos', 'cantidad_entrada_kg')) {
                $table->decimal('cantidad_entrada_kg', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'cantidad_salida_kg')) {
                $table->decimal('cantidad_salida_kg', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'merma_kg')) {
                $table->decimal('merma_kg', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'merma_porcentaje')) {
                $table->decimal('merma_porcentaje', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'temperatura_proceso')) {
                $table->decimal('temperatura_proceso', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'tiempo_proceso_horas')) {
                $table->decimal('tiempo_proceso_horas', 6, 2)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'metodo_proceso')) {
                $table->string('metodo_proceso', 100)->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'estado')) {
                $table->enum('estado', ['INICIADO', 'EN_PROCESO', 'FINALIZADO', 'CANCELADO'])->default('INICIADO')->nullable();
            }
            if (!Schema::hasColumn('control_procesos', 'producto_id')) {
                $table->foreignId('producto_id')->nullable()->constrained('productos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('control_procesos', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_entrada_kg',
                'cantidad_salida_kg',
                'merma_kg',
                'merma_porcentaje',
                'estado',
                'temperatura_proceso',
                'tiempo_proceso_horas',
                'metodo_proceso'
            ]);
        });
    }
};
