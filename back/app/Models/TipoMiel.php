<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class TipoMiel extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;
    protected $table = 'tipo_miel';
    protected $fillable = ['tipo_miel'];
    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function colmenas()
    {
        return $this->hasMany(Colmena::class, 'tipo_miel_id');
    }
}
