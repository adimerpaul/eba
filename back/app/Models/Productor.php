<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Productor extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'productores';

    protected $fillable = [
        'municipio_id',
        'runsa',
        'sub_codigo',
        'nombre',
        'apellidos',
        'numcarnet',
        'expedido',
        'fec_nacimiento',
        'sexo',
        'direccion',
        'comunidad',
        'proveedor',
        'cip_acopio',
        'num_celular',
        'ocupacion',
        'otros',
        'seleccion',
        'fecha_registro',
        'organizacion_id',
        'fecha_expiracion',
        'estado',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    // Relaciones
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'organizacion_id');
    }

    // Accesor de nombre completo (útil en la tabla)
    protected $appends = ['nombre_completo'];

    public function getNombreCompletoAttribute()
    {
        return trim(($this->nombre ?? '').' '.($this->apellidos ?? ''));
    }
    public function certificaciones()
    {
        return $this->hasMany(Certificacion::class, 'productor_id');
    }
}
