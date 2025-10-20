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
        Schema::create('control_procesos', function (Blueprint $table) {
            $table->id();
//            CREATE TABLE public.control_procesos (
//                id bigserial NOT NULL PRIMARY KEY,
//	cosecha_id INTEGER REFERENCES public.acopio_cosechas(id),
//	producto_id INTEGER REFERENCES public.productos(id),
//	tanque_id INTEGER REFERENCES public.tanques(id),
//	fecha_proceso timestamp NOT null default now(),
//	dato1 numeric(7, 2) NULL,
//	dato2 numeric(7, 2) NULL,
//	dato3 numeric(7, 2) NULL,
//	dato4 numeric(7, 2) NULL,
//	dato5 numeric(7, 2) NULL,
//	dato6 numeric(7, 2) NULL,
//	dato7 numeric(7, 2) NULL,
//	dato8 numeric(7, 2) NULL,
//	dato9 numeric(7, 2) NULL,
//	dato10 numeric(7, 2) NULL,
//	dato11 numeric(7, 2) NULL,
//	dato12 numeric(7, 2) NULL,
//	dato13 numeric(7, 2) NULL,
//	dato14 numeric(7, 2) NULL,
//	dato15 numeric(7, 2) NULL,
//	usuario_id int4 NULL,
//	observaciones varchar(100) NULL
//);
//
//COMMENT ON TABLE public.control_procesos IS 'Table contiene registro de todas los datos consernientes al proceso de descristalizacion de la miel';
//COMMENT ON COLUMN public.control_procesos.id IS 'clave  ID clave unico de la tabla autoincrementable';
//COMMENT ON COLUMN public.control_procesos.cosecha_id IS 'ID clave foranea relacionado con la acopio_cosechas';
//COMMENT ON COLUMN public.control_procesos.producto_id IS 'ID clave foranea relacionado con productos';
//COMMENT ON COLUMN public.control_procesos.tanque_id IS 'ID clave foranea relacionado con la tanques';
//COMMENT ON COLUMN public.control_procesos.fecha_proceso IS 'ID clave foranea relacionado con la tanques';
//COMMENT ON COLUMN public.control_procesos.dato1 IS 'corresponde al primer dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato2 IS 'corresponde al 2 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato3 IS 'corresponde al 3 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato4 IS 'corresponde al 4 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato5 IS 'corresponde al 5 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato6 IS 'corresponde al 6 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato7 IS 'corresponde al 7 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato8 IS 'corresponde al 8 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato9 IS 'corresponde al 9 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato10 IS 'corresponde al 10 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato11 IS 'corresponde al 11 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato12 IS 'corresponde al 12 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato13 IS 'corresponde al 13 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato14 IS 'corresponde al 14 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.dato15 IS 'corresponde al 15 dato de registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.usuario_id IS 'corresponde al personal que realiza el registro en la descristalizacion';
//COMMENT ON COLUMN public.control_procesos.observaciones IS 'corresponde las observacviones en registro en la descristalizacion';

            $table->unsignedBigInteger('cosecha_id');
            $table->foreign('cosecha_id')->references('id')->on('acopio_cosechas');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos');
            $table->unsignedBigInteger('tanque_id');
            $table->foreign('tanque_id')->references('id')->on('tanques');
            $table->timestamp('fecha_proceso')->default(DB::raw('now()'));
            $table->decimal('dato1', 7, 2)->nullable();
            $table->decimal('dato2', 7, 2)->nullable();
            $table->decimal('dato3', 7, 2)->nullable();
            $table->decimal('dato4', 7, 2)->nullable();
            $table->decimal('dato5', 7, 2)->nullable();
            $table->decimal('dato6', 7, 2)->nullable();
            $table->decimal('dato7', 7, 2)->nullable();
            $table->decimal('dato8', 7, 2)->nullable();
            $table->decimal('dato9', 7, 2)->nullable();
            $table->decimal('dato10', 7, 2)->nullable();
            $table->decimal('dato11', 7, 2)->nullable();
            $table->decimal('dato12', 7, 2)->nullable();
            $table->decimal('dato13', 7, 2)->nullable();
            $table->decimal('dato14', 7, 2)->nullable();
            $table->decimal('dato15', 7, 2)->nullable();
            $table->unsignedSmallInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('observaciones', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_procesos');
    }
};
