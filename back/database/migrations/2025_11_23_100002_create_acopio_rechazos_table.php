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
        Schema::create('acopio_rechazos', function (Blueprint $table) {
            $table->id();
            
            // Relación con acopio
            $table->foreignId('acopio_cosecha_id')->constrained('acopio_cosechas')->onDelete('cascade')
                ->comment('Acopio que fue rechazado');
            
            // Motivo del rechazo
            $table->enum('motivo_rechazo', [
                'CALIDAD_INSUFICIENTE',
                'HUMEDAD_ALTA',
                'CONTAMINACION',
                'DOCUMENTACION_INCOMPLETA',
                'TEMPERATURA_INCORRECTA',
                'PESO_INCORRECTO',
                'ENVASE_INADECUADO',
                'OTRO'
            ])->comment('Motivo principal del rechazo');
            
            // Detalles
            $table->text('observaciones')->nullable()->comment('Observaciones detalladas del rechazo');
            $table->text('accion_correctiva')->nullable()->comment('Acción correctiva sugerida al productor');
            
            // Usuario que rechazó
            $table->foreignId('rechazado_por')->constrained('users')->comment('Usuario que realizó el rechazo');
            
            // Estado de devolución
            $table->enum('estado_devolucion', ['PENDIENTE', 'NOTIFICADO', 'DEVUELTO', 'CANCELADO'])->default('PENDIENTE')
                ->comment('Estado de la devolución al productor');
            
            // Fechas
            $table->timestamp('fecha_rechazo')->useCurrent()->comment('Fecha y hora del rechazo');
            $table->timestamp('fecha_notificacion')->nullable()->comment('Fecha en que se notificó al productor');
            $table->timestamp('fecha_devolucion')->nullable()->comment('Fecha en que se devolvió el material');
            
            // Usuario que gestionó la devolución
            $table->foreignId('devuelto_por')->nullable()->constrained('users')
                ->comment('Usuario que marcó como devuelto');
            
            // Evidencia (opcional)
            $table->json('evidencias')->nullable()->comment('URLs de fotos u otros documentos (JSON array)');
            
            // Log relacionado (si aplica)
            $table->foreignId('procesamiento_masivo_log_id')->nullable()
                ->constrained('procesamiento_masivo_logs')->onDelete('set null')
                ->comment('Log de procesamiento masivo donde se rechazó (si aplica)');
            
            // Auditoría
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('acopio_cosecha_id');
            $table->index('motivo_rechazo');
            $table->index('estado_devolucion');
            $table->index('fecha_rechazo');
            $table->index(['rechazado_por', 'estado_devolucion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acopio_rechazos');
    }
};
