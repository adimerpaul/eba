<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AcopioCosecha extends Model implements Auditable{
    use SoftDeletes, AuditableTrait;
//Schema::create('acopio_cosechas', function (Blueprint $table) {
//    $table->id();
//
//    // Datos base
//    $table->date('fecha_cosecha');
//
//    // Relaciones
//    $table->unsignedBigInteger('apiario_id')->nullable();
//    $table->foreign('apiario_id')->references('id')->on('apiarios');
//
//    $table->unsignedBigInteger('producto_id')->default(1);
//    $table->foreign('producto_id')->references('id')->on('productos');
//
//    // Métricas de acopio
//    $table->decimal('cantidad_kg', 10, 2)->nullable();          // cantidad recibida
//    $table->decimal('precio_compra', 10, 2)->default(32);        // precio por kg (o unidad)
//    $table->decimal('humedad', 5, 2)->nullable();
//    $table->decimal('temperatura_almacenaje', 5, 2)->nullable();
//
//    // Documentos / tracking
//    $table->string('num_acta', 100)->default('0');
//
//    // Observaciones y procedencia/envase
//    $table->string('observaciones', 255)->nullable();
//    $table->string('procedencia', 50)->nullable();
//    $table->string('tipo_envase', 100)->nullable();
//
//    // Estado operativo (BUENO | EN_PROCESO | CANCELADO, etc.)
//    $table->string('estado', 20)->default('BUENO');
//
//    // Control
//    $table->softDeletes();
//    $table->timestamps();
//
//    // Índices útiles
//    $table->index(['fecha_cosecha']);
//    $table->index(['estado']);
//});
    protected $fillable = [
        'fecha_cosecha',
        'apiario_id',
        'producto_id',
        'cantidad_kg',
        'precio_compra',
        'humedad',
        'temperatura_almacenaje',
        'num_acta',
        'observaciones',
        'procedencia',
        'tipo_envase',
        'estado',
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    function apiario(){
        return $this->belongsTo(Apiario::class,'apiario_id');
    }
    function producto(){
        return $this->belongsTo(Producto::class,'producto_id');
    }
    public function analisisCalidad()
    {
        return $this->hasMany(\App\Models\AnalisisCalidad::class, 'cosecha_id');
    }
}
