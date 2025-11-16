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
        Schema::create('plagas', function (Blueprint $table) {
            $table->id();
            // Campos según formulario físico "Registro de control de plagas en colmenas"
            $table->date('fecha');
            $table->string('numero_colmenas_apiario', 100)->nullable();
            $table->string('nombre_plaga', 100);
            $table->string('plaga_presente', 100)->nullable();
            $table->string('daño_visible_apiario', 255)->nullable();
            $table->string('medidas_control_celdilla', 255)->nullable();
            $table->string('observaciones', 255)->nullable();
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
        Schema::dropIfExists('plagas');
    }
};
