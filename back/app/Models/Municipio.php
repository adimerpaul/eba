<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Municipio extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;
    protected $fillable = [
        'nombre_municipio',
        'provincia_id',
        'departamento_id',
        'zona',
        'region'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
}
