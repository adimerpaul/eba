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
            $table->dateTime('fecha_registro');
            $table->string('equipo', 200);
            $table->string('material', 200);
            $table->string('metodo_limpieza', 255)->nullable(); 
            $table->string('producto_limpieza', 255)->nullable(); 
            $table->unsignedBigInteger('apiario_id');
            $table->foreign('apiario_id')->references('id')->on('apiarios');
            $table->softDeletes(); // delete
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
