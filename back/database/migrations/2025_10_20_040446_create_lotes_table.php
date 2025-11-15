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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
//            CREATE TABLE public.lotes (
//                id bigserial NOT NULL PRIMARY KEY,
//	cosecha_id INTEGER REFERENCES public.acopio_cosechas(id),
//	tanque_id INTEGER REFERENCES public.tanques(id),
//	producto_id INTEGER REFERENCES public.productos(id),
//	codigo_lote varchar(100) NULL,
//	cantidad_kg numeric(10, 2) NULL,
//	fecha_envasado date NULL,
//	fecha_caducidad date NULL,
//	tipo_envase varchar(50) NULL,
//	CONSTRAINT lotes_codigo_lote_key UNIQUE (codigo_lote)
//);
//CREATE INDEX idx_lotes_cosecha ON public.lotes USING btree (cosecha_id);
//CREATE INDEX idx_lotes_fecha ON public.lotes USING btree (fecha_envasado);
//
//COMMENT ON TABLE public.lotes IS 'Tabla registra los lotes de los productos finales, ademas de fechas de vencimento y otros';
//COMMENT ON COLUMN public.lotes.id IS 'clave  ID clave unico de la tabla autoincrementable';
//COMMENT ON COLUMN public.lotes.cosecha_id IS 'ID clave foranea relacionado con la tabla acopio_cosechas';
//COMMENT ON COLUMN public.lotes.producto_id IS 'ID clave foranea relacionado con la tabla productos';
//COMMENT ON COLUMN public.lotes.tanque_id IS 'ID clave foranea relacionado con la tabla tanques';
//COMMENT ON COLUMN public.lotes.codigo_lote IS 'codigo del lote que es generado por numero planta - numero producto - lote correlativo - numero de tanque';
//COMMENT ON COLUMN public.lotes.cantidad_kg IS 'se registra la cantidad en Kg que se esta registrando';
//COMMENT ON COLUMN public.lotes.fecha_envasado IS 'se registra la fecha de procesamiento del producto final';
//COMMENT ON COLUMN public.lotes.fecha_caducidad IS 'se registra la fecha de caducidad o vencimiento del producto';
//COMMENT ON COLUMN public.lotes.tipo_envase IS 'tipo de envase vaso vidrio, sachet, etc.';

            $table->unsignedBigInteger('cosecha_id');
            $table->foreign('cosecha_id')->references('id')->on('acopio_cosechas');
            $table->unsignedBigInteger('tanque_id');
            $table->foreign('tanque_id')->references('id')->on('tanques');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->string('codigo_lote', 100)->unique()->nullable();
            $table->decimal('cantidad_kg', 10, 2)->nullable();
            $table->date('fecha_envasado')->nullable();
            $table->date('fecha_caducidad')->nullable();
            $table->string('tipo_envase', 50)->nullable();
            $table->string('qr_code', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
