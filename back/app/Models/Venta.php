<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Venta extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

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
    protected $fillable = [
        'cliente_id',
        'transporte_id',
        'fecha_venta',
        'precio_total',
        'destino_final',
        'guia_remision',
        'num_factura',
        'estado',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    function transporte()
    {
        return $this->belongsTo(Transporte::class);
    }
    function detalles()
    {
        return $this->hasMany(Kardex::class, 'venta_id');
    }
}
