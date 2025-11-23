<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración para tabla de control de transporte de acopios.
     * Registra información detallada del transporte de materia prima desde apiarios.
     * Cumple con requisitos SENASAG para trazabilidad de cadena de frío.
     */
    public function up(): void
    {
        Schema::create('acopio_transporte_log', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->unsignedBigInteger('acopio_cosecha_id');
            $table->foreign('acopio_cosecha_id')->references('id')->on('acopio_cosechas')->onDelete('cascade');
            
            $table->unsignedBigInteger('transporte_id');
            $table->foreign('transporte_id')->references('id')->on('transportes')->onDelete('restrict');
            
            // Información de origen y destino
            $table->string('lugar_origen', 200)->comment('Ubicación de origen del transporte (apiario)');
            $table->string('lugar_destino', 200)->default('Planta de procesamiento')->comment('Ubicación de destino');
            $table->decimal('distancia_km', 7, 2)->nullable()->comment('Distancia recorrida en kilómetros');
            
            // Control de temperatura (requisito SENASAG)
            $table->decimal('temperatura_salida', 5, 2)->nullable()->comment('Temperatura al inicio del transporte en °C');
            $table->decimal('temperatura_llegada', 5, 2)->nullable()->comment('Temperatura al final del transporte en °C');
            $table->decimal('temperatura_maxima', 5, 2)->nullable()->comment('Temperatura máxima registrada durante transporte en °C');
            $table->decimal('temperatura_minima', 5, 2)->nullable()->comment('Temperatura mínima registrada durante transporte en °C');
            
            // Control de tiempo
            $table->timestamp('fecha_hora_salida')->nullable()->comment('Fecha y hora de salida del origen');
            $table->timestamp('fecha_hora_llegada')->nullable()->comment('Fecha y hora de llegada al destino');
            $table->decimal('tiempo_transporte_horas', 5, 2)->nullable()->comment('Duración total del transporte en horas');
            
            // Condiciones del transporte
            $table->string('condiciones_envase', 50)->nullable()->comment('Estado del envase (Limpio, Sellado, Adecuado, etc)');
            $table->string('condiciones_vehiculo', 50)->nullable()->comment('Estado del vehículo (Limpio, Adecuado, Necesita limpieza, etc)');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales sobre el transporte');
            
            // Control de alertas automáticas
            $table->boolean('alerta_temperatura')->default(false)->comment('Indica si se excedió límite de temperatura');
            $table->boolean('alerta_tiempo')->default(false)->comment('Indica si se excedió tiempo máximo permitido');
            
            // Registro y auditoría
            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps();
            
            // Índices para mejorar rendimiento en consultas
            $table->index('acopio_cosecha_id');
            $table->index('transporte_id');
            $table->index(['fecha_hora_salida', 'fecha_hora_llegada']);
            $table->index(['alerta_temperatura', 'alerta_tiempo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acopio_transporte_log');
    }
};
