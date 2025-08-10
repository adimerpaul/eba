<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Apicultor extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;
    protected $table = 'apicultores';

    protected $fillable = [
        'codigo','nombre','ci','telefono','email',
        'departamento','municipio','asociacion','estado',
        'apiarios','ultima_inspeccion','lat','lng','observaciones'
    ];

//    protected $casts = [
//        'ultima_inspeccion' => 'date',
//        'apiarios' => 'integer',
//        'lat' => 'decimal:7',
//        'lng' => 'decimal:7',
//    ];

    // Evitar auditar datos sensibles si luego agregas más campos
    protected $auditExclude = [];
}
