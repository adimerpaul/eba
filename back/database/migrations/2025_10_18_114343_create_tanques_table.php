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
        Schema::create('tanques', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_tanque', 5);
            $table->string('nombre_tanque', 150);
            $table->unsignedBigInteger('planta_id')->nullable();
            $table->foreign('planta_id')->references('id')->on('plantas');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanques');
    }
};
