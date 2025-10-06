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
        Schema::create('apiarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('productor_id')->constrained('productores');
            $table->foreignId('tipo_manejo_id')->nullable()->constrained('tipo_manejos');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');

            $table->string('nombre_cip', 100)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->integer('altitud')->nullable();
            $table->string('lugar_apiario', 200)->nullable();

            $table->integer('numero_colmenas_runsa')->nullable();
            $table->integer('numero_colmenas_prod')->default(0);
            $table->unsignedBigInteger('seleccion')->default(0);
            $table->decimal('rend_programa_nal', 7, 2)->default(0);
            $table->unsignedBigInteger('organizacion_id')->default(0);

            $table->date('fecha_instalacion')->nullable();
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO/INACTIVO
            $table->string('fase', 50)->nullable();
            $table->string('coordenada', 50)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['productor_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apiarios');
    }
};
