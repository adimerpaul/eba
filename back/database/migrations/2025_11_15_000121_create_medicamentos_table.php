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
            $table->dateTime('fecha_registro');
            $table->string('nombre_producto', 100);
            $table->string('activo', 100)->nullable();
            $table->string('recomendada', 200)->nullable();
            $table->string('aplicada', 200)->nullable();
            $table->string('plagas', 200)->nullable();
            $table->string('periodo', 100)->nullable();
            $table->string('encargado', 200)->nullable();
            $table->unsignedBigInteger('apiario_id');
            $table->foreign('apiario_id')->references('id')->on('apiarios');
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
