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
        Schema::create('plantas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('registro_sanitario', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->foreign('municipio_id')->references('id')->on('municipios');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantas');
    }
};
