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
        Schema::create('apicultores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();          // ej. API-001
            $table->string('nombre');                    // Juan Pérez
            $table->string('ci')->nullable();            // documento
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('asociacion')->nullable();    // asociación/cooperativa
            $table->enum('estado', ['Activo','Mantenimiento', 'Inactivo'])->default('Activo');

            // datos operativos
            $table->unsignedInteger('apiarios')->default(0);
            $table->date('ultima_inspeccion')->nullable();

            // georreferencia opcional (centroide del productor)
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apicultores');
    }
};
