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
            $table->dateTime('fecha_registro');
            $table->string('nombre_plaga', 100);
            $table->string('descripcion', 255)->nullable();
            $table->string('medidas_control', 255)->nullable();
            $table->string('observacion', 255)->nullable();
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
        Schema::dropIfExists('plagas');
    }
};
