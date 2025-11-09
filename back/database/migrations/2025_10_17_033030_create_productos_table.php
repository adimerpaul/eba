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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
//            CREATE TABLE public.productos (
//                id bigserial NOT NULL PRIMARY KEY,
//	tipo_id INTEGER REFERENCES public.tipo_productos(id),
//	codigo_producto varchar(20) NOT NULL,
//	nombre_producto varchar(100) NOT NULL,
//	presentacion varchar(20)  DEFAULT 'PIEZA'::character varying NOT NULL,
//	cantidad_kg numeric(10, 2) NULL,
//	costo numeric(10, 2) default 0,
//	precio numeric(10, 2) default 0,
//	fecha_vencimiento date NULL,
//	nro_lote varchar(20) null,
//	codigo_barra varchar(20) null,
//	imagen varchar(100) null
//);
            $table->unsignedBigInteger('tipo_id');
            $table->foreign('tipo_id')->references('id')->on('tipo_productos');
            $table->string('codigo_producto', 20);
            $table->string('nombre_producto', 100);
            $table->string('presentacion', 20)->default('PIEZA');
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->decimal('costo', 10, 2)->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('nro_lote', 20)->nullable();
            $table->string('codigo_barra', 20)->nullable();
            $table->string('imagen', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
