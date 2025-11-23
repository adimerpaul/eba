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
        Schema::create('procesamiento_masivo_logs', function (Blueprint $table) {
            $table->id();
            
            // Usuario que realizó el procesamiento
            $table->foreignId('usuario_id')->constrained('users')->comment('Usuario que ejecutó el procesamiento');
            
            // Tipo de procesamiento
            $table->enum('tipo_procesamiento', ['AUTOMATICO', 'MANUAL', 'SELECCION'])->default('MANUAL')
                ->comment('Tipo de procesamiento: AUTOMATICO (todos), MANUAL (selección), SELECCION (con filtros)');
            
            // Métricas del procesamiento
            $table->integer('acopios_procesados')->default(0)->comment('Cantidad de acopios procesados exitosamente');
            $table->integer('acopios_rechazados')->default(0)->comment('Cantidad de acopios rechazados');
            $table->integer('acopios_fallidos')->default(0)->comment('Cantidad de acopios que fallaron al procesar');
            
            // Totales
            $table->decimal('total_kg_procesado', 12, 2)->default(0)->comment('Total de kilogramos procesados');
            $table->decimal('total_costo', 12, 2)->default(0)->comment('Costo total de los acopios procesados');
            
            // Filtros aplicados (JSON)
            $table->json('filtros_aplicados')->nullable()->comment('Filtros que se aplicaron en el procesamiento (JSON)');
            
            // Lista de IDs procesados (JSON)
            $table->json('acopio_ids')->nullable()->comment('IDs de los acopios procesados (JSON array)');
            
            // Observaciones
            $table->text('observaciones')->nullable()->comment('Observaciones o notas del procesamiento');
            
            // Metadata
            $table->timestamp('fecha_ejecucion')->useCurrent()->comment('Fecha y hora de ejecución');
            $table->integer('duracion_segundos')->nullable()->comment('Duración del procesamiento en segundos');
            
            // Auditoría
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimización
            $table->index('usuario_id');
            $table->index('tipo_procesamiento');
            $table->index('fecha_ejecucion');
            $table->index(['created_at', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesamiento_masivo_logs');
    }
};
