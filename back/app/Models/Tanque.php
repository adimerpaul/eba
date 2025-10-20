<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Tanque extends Model implements Auditable
{
    use AuditableTrait,SoftDeletes;
    //            $table->id();
    //            $table->string('codigo_tanque', 5);
    //            $table->string('nombre_tanque', 150);
    //            $table->unsignedBigInteger('planta_id')->nullable();
    //            $table->foreign('planta_id')->references('id')->on('plantas');
    //            $table->softDeletes();
    //            $table->timestamps();
    protected $fillable = [
        'codigo_tanque',
        'nombre_tanque',
        'planta_id'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function planta()
    {
        return $this->belongsTo(Planta::class);
    }
}
