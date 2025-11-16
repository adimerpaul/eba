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
        Schema::create('limpiezas', function (Blueprint $table) {
            $table->id();
            // Campos según formulario físico "Registro de limpieza y desinfección de equipos y herramienta apícolas"
            $table->string('equipo_herramienta_material', 200);
            $table->string('material_recubrimiento', 200)->nullable();
            $table->string('metodo_limpieza_utilizado', 255)->nullable();
            $table->string('producto_quimico_desinfeccion', 255)->nullable();
            $table->date('fecha_aplicacion');
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
        Schema::dropIfExists('limpiezas');
    }
};
