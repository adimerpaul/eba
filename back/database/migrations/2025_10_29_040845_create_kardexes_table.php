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
        Schema::create('kardex', function (Blueprint $table) {
//            CREATE TABLE kardex (
//                id bigserial NOT NULL PRIMARY KEY,
//	lote_id INTEGER REFERENCES public.lotes(id),
//	producto_id INTEGER REFERENCES public.productos(id),
//	venta_id INTEGER REFERENCES public.ventas(id),
//	fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//	cantidad_procesada numeric(10, 2) null,
//	cantidad_salida numeric(10, 2) null,
//	precio_venta numeric(10, 2) DEFAULT 0 NOT NULL,
//	usuario_id INTEGER REFERENCES public.users(id)
//);
            $table->id();
            $table->unsignedBigInteger('lote_id');
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('venta_id');
            $table->timestamp('fecha_registro')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->decimal('cantidad_procesada', 10, 2)->nullable();
            $table->decimal('cantidad_salida', 10, 2)->nullable();
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->unsignedBigInteger('user_id');
            $table->foreign('lote_id')->references('id')->on('lotes');
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->foreign('venta_id')->references('id')->on('ventas');
            $table->foreign('user_id')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
