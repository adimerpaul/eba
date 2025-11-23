<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_proceso_acopios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('control_proceso_id');
            $table->unsignedBigInteger('acopio_cosecha_id');
            $table->decimal('cantidad_kg', 10, 2);
            $table->timestamps();

            $table->foreign('control_proceso_id')->references('id')->on('control_procesos')->onDelete('cascade');
            $table->foreign('acopio_cosecha_id')->references('id')->on('acopio_cosechas')->onDelete('cascade');
            
            $table->index(['control_proceso_id', 'acopio_cosecha_id'], 'idx_control_acopio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_proceso_acopios');
    }
};
