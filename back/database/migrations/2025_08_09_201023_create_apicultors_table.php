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
//            ID	Latitud 	Longitud 	CODIGO RUNSA 	SUBCODIGO 	RUNSA 	NOMBRE Y APELLIDO 	CI	EXPEDIDO 	CELULAR	LUGAR/APIARIO (COMUNIDAD)	N° DE COLMENAS CON RUNSA	N° DE COLMENAS EN PRODUCCIÓN	PRODUCCIÓN PROMEDIO (kg x Colmena)  	PROYECCIÓN PRODUCCIÓN TOTAL (kg)	PROYECCION PRODUCCION TONELADAS /AÑO	ASOCIACIÓN 	FOMENTO 	FORTALECIMIENTO	TOTAL BENEFICIARIOS 	NATIVAS	FOM	FORT	SUMA NUEVOS	N° ACTA	LOTE

            $table->id();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('codigo_runsa')->nullable();
            $table->string('subcodigo')->nullable();
            $table->string('runsa')->nullable();
            $table->string('nombre_apellido')->nullable();
            $table->string('ci')->nullable();
            $table->string('expedido')->nullable();
            $table->string('celular')->nullable();
            $table->string('lugar_apiario')->nullable();
            $table->integer('n_colmenas_runsa')->nullable();
            $table->integer('n_colmenas_produccion')->nullable();
            $table->float('produccion_promedio')->nullable();
            $table->float('proyeccion_produccion_total')->nullable();
            $table->float('proyeccion_produccion_toneladas')->nullable();
            $table->string('asociacion')->nullable();
            $table->integer('fomento')->nullable();
            $table->integer('fortalecimiento')->nullable();
            $table->integer('total_beneficiarios')->nullable();
            $table->integer('nativas')->nullable();
            $table->integer('fom')->nullable();
            $table->integer('fort')->nullable();
            $table->integer('suma_nuevos')->nullable();
            $table->string('n_acta')->nullable();
            $table->string('lote')->nullable();
            $table->enum('estado', ['Activo','Mantenimiento', 'Inactivo'])->default('Activo');
            $table->softDeletes();
            $table->timestamps();
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
