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
        Schema::create('clientes', function (Blueprint $table) {
//            CREATE TABLE clientes (
//                id bigserial NOT NULL PRIMARY KEY,
//	nit varchar(13) NULL,
//	nombre_cliente varchar(200) NOT NULL,
//	direccion text NULL,
//	telefono varchar(15) NULL,
//	email varchar(100) NULL,
//	pais_destino varchar(100) NULL,
//	CONSTRAINT clientes_nit_key UNIQUE (nit)
//);
            $table->id();
            $table->string('nit', 13)->nullable();
            $table->string('nombre_cliente', 200);
            $table->text('direccion')->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('pais_destino', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
