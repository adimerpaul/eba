<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('municipio_id')->nullable()->index();
            $table->foreign('municipio_id')->references('id')->on('municipios');

            $table->string('nombre_organiza', 50);
            $table->string('asociacion', 50);
            $table->string('programa', 50)->nullable();
            $table->string('nombre_presidente', 50)->nullable();
            $table->string('descripcion', 250)->nullable();
            $table->string('celular', 20)->nullable();
            $table->integer('num_apicultor')->nullable();
            $table->integer('num_colmena')->nullable();

            $table->integer('pj_actual')->default(0);
            $table->integer('convenio')->default(0);

            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO|INACTIVO
            $table->date('fecha_registro')->default(DB::raw('CURRENT_DATE'));

            $table->softDeletes();
            $table->timestamps();

            // CHECK para estado (Postgres)
//            $table->check("estado in ('ACTIVO','INACTIVO')");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizaciones');
    }
};
