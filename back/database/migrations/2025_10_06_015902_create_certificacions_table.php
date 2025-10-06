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
        Schema::create('certificaciones', function (Blueprint $table) {
            $table->id(); // id
            $table->unsignedBigInteger('productor_id')->index();
            $table->string('tipo_certificacion', 100)->nullable(); // validado con CHECK debajo (PostgreSQL)
            $table->string('organismo_certificador', 200)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('certificado_url')->nullable();
            $table->string('estado', 20)->default('VIGENTE'); // CHECK debajo
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('productor_id')->references('id')->on('productores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificaciones');
    }
};
