<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Certificacion extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'certificaciones';

    protected $fillable = [
        'productor_id',
        'tipo_certificacion',
        'organismo_certificador',
        'fecha_emision',
        'fecha_vencimiento',
        'certificado_url',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function productor()
    {
        return $this->belongsTo(Productor::class, 'productor_id');
    }
}
