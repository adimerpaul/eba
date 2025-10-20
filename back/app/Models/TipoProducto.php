<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class TipoProducto extends Model implements Auditable{
    use AuditableTrait, SoftDeletes;
    protected $fillable = [
        'codigo_tipo',
        'nombre_tipo',
        'detalles'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}
