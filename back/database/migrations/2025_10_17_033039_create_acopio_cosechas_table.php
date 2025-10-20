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
        Schema::create('acopio_cosechas', function (Blueprint $table) {
            $table->id();

            // Datos base
            $table->date('fecha_cosecha');

            // Relaciones
            $table->unsignedBigInteger('apiario_id')->nullable();
            $table->foreign('apiario_id')->references('id')->on('apiarios');

            $table->unsignedBigInteger('producto_id')->default(1);
            $table->foreign('producto_id')->references('id')->on('productos');

            // Métricas de acopio
            $table->decimal('cantidad_kg', 10, 2)->nullable();          // cantidad recibida
            $table->decimal('precio_compra', 10, 2)->default(32);        // precio por kg (o unidad)
            $table->decimal('humedad', 5, 2)->nullable();
            $table->decimal('temperatura_almacenaje', 5, 2)->nullable();

            // Documentos / tracking
            $table->string('num_acta', 100)->default('0');

            // Observaciones y procedencia/envase
            $table->string('observaciones', 255)->nullable();
            $table->string('procedencia', 50)->nullable();
            $table->string('tipo_envase', 100)->nullable();

            // Estado operativo (BUENO | EN_PROCESO | CANCELADO, etc.)
            $table->string('estado', 20)->default('BUENO');

            // Control
            $table->softDeletes();
            $table->timestamps();

            // Índices útiles
            $table->index(['fecha_cosecha']);
            $table->index(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acopio_cosechas');
    }
};
