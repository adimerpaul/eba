<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class Planta extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'traza.plantas';

    protected $fillable = [
        'codigo_planta',
        'nombre_planta',
        'registro_sanitario',
        'direccion',
        'municipio_id'
    ];

    protected $hidden = ['created_at','updated_at','deleted_at'];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }
    //            $table->string('codigo_planta', 10)->unique();
    //            $table->string('nombre_planta', 150);
    //            $table->string('registro_sanitario', 50)->nullable();
    //            $table->text('direccion')->nullable();
    //            $table->unsignedBigInteger('municipio_id')->nullable();
    //            $table->foreign('municipio_id')->references('id')->on('municipios');
    //            $table->softDeletes();
}
