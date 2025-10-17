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
            $table->date('fecha_cosecha');
            $table->unsignedBigInteger('apiario_id')->nullable();
            $table->foreign('apiario_id')->references('id')->on('apiarios');
            $table->decimal('cantidad_kg', 10, 2)->nullable();
            $table->decimal('humedad', 5, 2)->nullable();
            $table->decimal('temperatura_almacenaje', 5, 2)->nullable();
            $table->string('num_acta', 100)->default('0');
            $table->string('condiciones_almacenaje', 100)->nullable();
            $table->string('estado', 20)->default('PENDIENTE')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
