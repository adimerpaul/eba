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
}
