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
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            // Campos según formulario físico "Registro de aplicación de medicamentos"
            $table->date('fecha');
            $table->string('nombre_producto', 100);
            $table->string('principio_activo', 100)->nullable();
            $table->string('dosis_recomendada', 200)->nullable();
            $table->string('dosis_aplicada', 200)->nullable();
            $table->string('plagas_controladas', 200)->nullable();
            $table->string('periodo_espera_cosecha', 100)->nullable();
            $table->string('nombre_encargado', 200)->nullable();
            $table->string('firma', 255)->nullable(); // Ruta de imagen o texto
            // Relación con acopio_cosechas en lugar de apiarios
            $table->unsignedBigInteger('acopio_cosecha_id');
            $table->foreign('acopio_cosecha_id')->references('id')->on('acopio_cosechas');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
