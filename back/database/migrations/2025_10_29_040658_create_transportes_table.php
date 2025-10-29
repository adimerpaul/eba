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
        Schema::create('transportes', function (Blueprint $table) {
            $table->id();
//            CREATE TABLE transportes (
//                id serial4 NOT NULL PRIMARY KEY,
//	empresa varchar(150) NULL,
//	placa varchar(20) NULL,
//	responsable varchar(150) NULL
//);
//COMMENT ON TABLE transportes IS 'Tabla registra los los transportes o empresas o conductores que llevaran la miel';
//COMMENT ON COLUMN transportes.id IS 'clave  ID clave unico de la tabla autoincrementable';
//COMMENT ON COLUMN transportes.empresa IS 'nombre o descripcion delconductor o empresa de transporte';
//COMMENT ON COLUMN transportes.placa IS 'numero de placa de la movilidad de transporte';
//COMMENT ON COLUMN transportes.responsable IS 'nombre o descripcion del conductor de transporte';
            $table->string('empresa', 150)->nullable();
            $table->string('placa', 20)->nullable();
            $table->string('responsable', 150)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportes');
    }
};
