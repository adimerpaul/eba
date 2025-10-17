<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

class AcopioCosecha extends Model implements \OwenIt\Auditing\Contracts\Auditable{
    use SoftDeletes, AuditableTrait;
//$table->date('fecha_cosecha');
//$table->unsignedBigInteger('id_apiario')->nullable();
//$table->foreign('id_apiario')->references('id')->on('apiarios');
//$table->decimal('cantidad_kg', 10, 2)->nullable();
//$table->decimal('humedad', 5, 2)->nullable();
//$table->decimal('temperatura_almacenaje', 5, 2)->nullable();
//$table->string('num_acta', 100)->default('0');
//$table->string('condiciones_almacenaje', 100)->nullable();
//$table->string('estado', 20)->default('PENDIENTE')->nullable();
    protected $fillable = [
        'fecha_cosecha',
        'apiario_id',
        'cantidad_kg',
        'humedad',
        'temperatura_almacenaje',
        'num_acta',
        'condiciones_almacenaje',
        'estado',
    ];
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    public function apiario(){
        return $this->belongsTo(Apiario::class, 'apiario_id');
    }
}
