<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productores', function (Blueprint $table) {
            $table->id(); // PK "id"

            // Relaciones
            $table->unsignedBigInteger('municipio_id')->nullable()->index();
            $table->foreign('municipio_id')->references('id')->on('municipios');

            // Datos principales
            $table->string('runsa', 20)->default('0')->index();
            $table->string('sub_codigo', 20)->nullable()->index();

            $table->string('nombre', 200);
            $table->string('apellidos', 200);
            $table->string('numcarnet', 20)->index();
            $table->string('expedido', 10)->nullable();

            $table->date('fec_nacimiento')->nullable();
            $table->integer('sexo')->nullable(); // 1=M, 2=F (por ejemplo)

            $table->text('direccion')->nullable();
            $table->string('comunidad', 100)->nullable();
            $table->string('proveedor', 50)->nullable();
            $table->string('cip_acopio', 50)->nullable();
            $table->string('num_celular', 15)->nullable();
            $table->string('ocupacion', 100)->nullable();
            $table->text('otros')->nullable();

            // campos numéricos / flags
            $table->bigInteger('seleccion')->default(0);
            $table->unsignedBigInteger('organizacion_id')->nullable()->index(); // mapeo de id_organiza
            // Si tienes tabla organizaciones, puedes agregar FK:
            // $table->foreign('organizacion_id')->references('id')->on('organizaciones');

            // Fechas/estado
            $table->date('fecha_registro')->default(DB::raw('CURRENT_DATE'))->index();
            $table->date('fecha_expiracion')->nullable()->index();
            $table->string('estado', 20)->default('VIGENTE')->index(); // VIGENTE|VENCIDO u otros

            // housekeeping
            $table->softDeletes();
            $table->timestamps();

            // Índices útiles para búsquedas grandes
            $table->index(['nombre', 'apellidos']);
            $table->index(['municipio_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productores');
    }
};
