<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Colmena extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;
    protected $fillable = [
        'apiario_id','tipo_miel_id','codigo_colmena','tipo_colmena',
        'fecha_instalacion','reina_fecha_nacimiento','reina_procedencia','estado'
    ];
    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function apiario()  { return $this->belongsTo(Apiario::class); }
    public function tipoMiel() { return $this->belongsTo(TipoMiel::class, 'tipo_miel_id'); }
}
