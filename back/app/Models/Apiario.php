<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Apiario extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'productor_id','municipio_id','tipo_manejo_id',
        'nombre_cip','latitud','longitud','altitud','lugar_apiario',
        'numero_colmenas_runsa','numero_colmenas_prod','seleccion','rend_programa_nal',
        'organizacion_id','fecha_instalacion','estado','fase','coordenada'
    ];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function productor()  { return $this->belongsTo(Productor::class); }
    public function municipio()  { return $this->belongsTo(Municipio::class); }
    public function colmenas()   { return $this->hasMany(Colmena::class); }
}
