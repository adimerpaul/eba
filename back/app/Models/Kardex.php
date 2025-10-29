<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Kardex extends Model implements Auditable{
    use AuditableTrait, SoftDeletes;
    protected $table = 'kardex';
    protected $fillable = [
        'lote_id',
        'producto_id',
        'venta_id',
        'fecha_registro',
        'cantidad_procesada',
        'cantidad_salida',
        'precio_venta',
        'user_id',
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    function lote()
    {
        return $this->belongsTo(Lote::class);
    }
    function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    function venta(){
        return $this->belongsTo(Venta::class);
    }
}
