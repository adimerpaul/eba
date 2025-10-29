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
        Schema::create('ventas', function (Blueprint $table) {
//            CREATE TABLE ventas (
//                id bigserial NOT NULL PRIMARY KEY,
//	cliente_id INTEGER REFERENCES public.clientes(id),
//	transporte_id INTEGER REFERENCES public.transportes(id),
//	fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//	precio_total numeric(10, 2) NULL,
//	destino_final varchar(200) NULL,
//	guia_remision varchar(100) NULL,
//	num_factura varchar(20) NULL
//);
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('transporte_id');
            $table->timestamp('fecha_venta')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('precio_total', 10, 2)->nullable();
            $table->string('destino_final', 200)->nullable();
            $table->string('guia_remision', 100)->nullable();
            $table->string('num_factura', 20)->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->foreign('transporte_id')->references('id')->on('transportes');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
