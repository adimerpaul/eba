<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Provincia extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'nombre_provincia',
        'departamento_id'
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

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
}
