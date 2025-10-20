<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class AnalisisCalidad extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;
    protected $table = 'analisis_calidad';
//    public function up(): void
//    {
//        Schema::create('analisis_calidad', function (Blueprint $table) {
//            $table->id();
////            CREATE TABLE public.analisis_calidad (
////                id bigserial NOT NULL PRIMARY KEY,
////	cosecha_id INTEGER REFERENCES public.acopio_cosechas(id),
////	fecha_analisis date NULL,
////	humedad numeric(5, 2) NULL,
////	ph numeric(4, 2) NULL,
////	brix numeric(5, 2) NULL,
////	color varchar(50) NULL,
////	sabor varchar(50) NULL,
////	olor varchar(50) NULL,
////	consistencia varchar(50) NULL,
////	porcentaje_polen numeric(5, 2) NULL,
////	conductividad_electrica numeric(8, 2) NULL,
////	glucosa numeric(5, 2) NULL,
////	fructosa numeric(5, 2) NULL,
////	sacarosa numeric(5, 2) NULL,
////	diastasa_enzimatica numeric(6, 2) NULL,
////	prolina_mgkg numeric(6, 2) NULL,
////	rotacion_especifica numeric(6, 2) NULL,
////	hidroximetilfurfural numeric(6, 2) NULL,
////	metodo_analisis varchar(50) NULL,
////	laboratorio varchar(200) NULL,
////	certificado_url text NULL,
////	observaciones text NULL,
////	cumplimiento_normativa bool DEFAULT true NULL
////);
//
//            $table->unsignedBigInteger('cosecha_id');
//            $table->foreign('cosecha_id')->references('id')->on('acopio_cosechas');
//            $table->date('fecha_analisis')->nullable();
//            $table->decimal('humedad', 5, 2)->nullable();
//            $table->decimal('ph', 4, 2)->nullable();
//            $table->decimal('brix', 5, 2)->nullable();
//            $table->string('color', 50)->nullable();
//            $table->string('sabor', 50)->nullable();
//            $table->string('olor', 50)->nullable();
//            $table->string('consistencia', 50)->nullable();
//            $table->decimal('porcentaje_polen', 5, 2)->nullable();
//            $table->decimal('conductividad_electrica', 8, 2)->nullable();
//            $table->decimal('glucosa', 5, 2)->nullable();
//            $table->decimal('fructosa', 5, 2)->nullable();
//            $table->decimal('sacarosa', 5, 2)->nullable();
//            $table->decimal('diastasa_enzimatica', 6, 2)->nullable();
//            $table->decimal('prolina_mgkg', 6, 2)->nullable();
//            $table->decimal('rotacion_especifica', 6, 2)->nullable();
//            $table->decimal('hidroximetilfurfural', 6, 2)->nullable();
//            $table->string('metodo_analisis', 50)->nullable();
//            $table->string('laboratorio', 200)->nullable();
//            $table->text('certificado_url')->nullable();
//            $table->text('observaciones')->nullable();
//            $table->boolean('cumplimiento_normativa')->default(true)->nullable();
//            $table->timestamps();
//            $table->softDeletes();
//        });
//    }

    protected $fillable = [
        'cosecha_id',
        'fecha_analisis',
        'humedad',
        'ph',
        'brix',
        'color',
        'sabor',
        'olor',
        'consistencia',
        'porcentaje_polen',
        'conductividad_electrica',
        'glucosa',
        'fructosa',
        'sacarosa',
        'diastasa_enzimatica',
        'prolina_mgkg',
        'rotacion_especifica',
        'hidroximetilfurfural',
        'metodo_analisis',
        'laboratorio',
        'certificado_url',
        'observaciones',
        'cumplimiento_normativa',
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    public function cosecha(){
        return $this->belongsTo(AcopioCosecha::class,'cosecha_id');
    }
}
