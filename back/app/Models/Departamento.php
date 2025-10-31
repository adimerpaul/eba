<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Departamento extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'nombre_departamento'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function provincias()
    {
        return $this->hasMany(Provincia::class);
    }

    public function municipios()
    {
        // útil si guardas departamento_id directo en municipios
        return $this->hasMany(Municipio::class);
    }
    public function productores()
    {
        // Departamento -> Municipios -> Productores
        return $this->hasManyThrough(
            \App\Models\Productor::class,
            \App\Models\Municipio::class,
            'departamento_id', // FK en municipios que apunta a departamentos
            'municipio_id',    // FK en productores que apunta a municipios
            'id',              // PK local en departamentos
            'id'               // PK local en municipios
        );
    }
    public function apiarios()
    {
        // Departamento -> Municipios -> Apiarios (vía Productor o Municipio)
        // Como Apiario tiene municipio_id, podemos ir directo:
        return $this->hasManyThrough(
            \App\Models\Apiario::class,
            \App\Models\Municipio::class,
            'departamento_id', // FK en municipios que apunta a departamentos
            'municipio_id',    // FK en apiarios que apunta a municipios
            'id',
            'id'
        );
    }
}
