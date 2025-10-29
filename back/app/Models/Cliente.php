<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Cliente extends Model implements Auditable{
    use AuditableTrait,SoftDeletes;
    //            CREATE TABLE clientes (
//                id bigserial NOT NULL PRIMARY KEY,
//	nit varchar(13) NULL,
//	nombre_cliente varchar(200) NOT NULL,
//	direccion text NULL,
//	telefono varchar(15) NULL,
//	email varchar(100) NULL,
//	pais_destino varchar(100) NULL
//);

    protected $fillable = [
        'nit',
        'nombre_cliente',
        'direccion',
        'telefono',
        'email',
        'pais_destino',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
