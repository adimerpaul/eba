<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Runsa extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'runsas';

    protected $fillable = [
        'codigo',
        'subcodigo',
        'fecha_registro',
        'fecha_vencimiento',
        'estado',
        'productor_id',
    ];
        protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    //
    public function productor()
    {
        return $this->belongsTo(Productor::class, 'productor_id');
    }
}
