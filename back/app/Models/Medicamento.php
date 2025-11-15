<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Medicamento extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;


    protected $fillable = [
        'fecha_registro',
        'nombre_producto',  
        'activo',
        'recomendada',
        'aplicada',
        'plagas',
        'periodo',
        'encargado',
        'apiario_id',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    //
    public function apiario()
    {
        return $this->belongsTo(Apiario::class, 'apiario_id');
    }
}
