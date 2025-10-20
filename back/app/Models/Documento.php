<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Documento extends Model implements Auditable
{

    use SoftDeletes, AuditableTrait;

    protected $table = 'documentos';

    protected $fillable = [
        'acopio_cosecha_id',
        'user_id',
        'html',
        'fecha',
    ];

    public function cosecha()
    {
        return $this->belongsTo(\App\Models\AcopioCosecha::class, 'acopio_cosecha_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
