<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Producto extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    //            $table->unsignedBigInteger('tipo_id');
    //            $table->foreign('tipo_id')->references('id')->on('tipo_productos');
    //            $table->string('codigo_producto', 20);
    //            $table->string('nombre_producto', 100);
    //            $table->string('presentacion', 20)->default('PIEZA');
    //            $table->decimal('cantidad_kg', 10, 2)->nullable();
    //            $table->decimal('costo', 10, 2)->default(0);
    //            $table->decimal('precio', 10, 2)->default(0);
    //            $table->date('fecha_vencimiento')->nullable();
    //            $table->string('nro_lote', 20)->nullable();
    //            $table->string('codigo_barra', 20)->nullable();
    //            $table->string('imagen', 100)->nullable();
    //            $table->softDeletes();
    protected $fillable = [
        'tipo_id',
        'codigo_producto',
        'nombre_producto',
        'presentacion',
        'cantidad_kg',
        'costo',
        'precio',
        'fecha_vencimiento',
        'nro_lote',
        'codigo_barra',
        'imagen'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    function  tipo(){
        return $this->belongsTo(TipoProducto::class, 'tipo_id');
    }
}
