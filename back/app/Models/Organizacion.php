<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Organizacion extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'organizaciones';

    protected $fillable = [
        'municipio_id',
        'nombre_organiza',
        'asociacion',
        'programa',
        'nombre_presidente',
        'descripcion',
        'celular',
        'num_apicultor',
        'num_colmena',
        'pj_actual',
        'convenio',
        'estado',
        'fecha_registro',
    ];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    // Relaciones
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function departamento()
    {
        // acceso rápido vía municipio->departamento
        return $this->hasOneThrough(
            Departamento::class,
            Municipio::class,
            'id',              // Municipio PK
            'id',              // Departamento PK
            'municipio_id',    // FK en organizaciones
            'departamento_id'  // FK en municipio
        );
    }

    public function provincia()
    {
        // si guardas provincia en municipio, exponemos helper
        return $this->hasOneThrough(
            Provincia::class,
            Municipio::class,
            'id',            // Municipio PK
            'id',            // Provincia PK
            'municipio_id',  // FK local
            'provincia_id'   // FK en municipio
        );
    }
}
