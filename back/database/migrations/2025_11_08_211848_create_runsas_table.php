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
        Schema::create('runsas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);
            $table->string('subcodigo', 20);
            $table->date('fecha_registro');
            $table->date('fecha_vencimiento');
            $table->string('estado', 20)->default('VIGENTE');
            $table->unsignedBigInteger('productor_id');
            $table->foreign('productor_id')->references('id')->on('productores');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runsas');
    }
};
