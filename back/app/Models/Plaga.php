<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo para registro de control de plagas en colmenas
 * Relacionado con acopio_cosechas para generar formularios con datos de cabecera
 */
class Plaga extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'traza.plagas';

    // Campos actualizados según formulario físico
    protected $fillable = [
        'fecha',
        'numero_colmenas_apiario',
        'nombre_plaga',
        'plaga_presente',
        'daño_visible_apiario',
        'medidas_control_celdilla',
        'observaciones',
        'acopio_cosecha_id',
    ];
    
    // Campos anteriores comentados para referencia
    // 'apiario_id',
    // 'fecha_registro',
    // 'descripcion',
    // 'medidas_control',
    // 'observacion',
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    // Relación actualizada: de apiario a acopio_cosecha
    public function acopioCosecha()
    {
        return $this->belongsTo(AcopioCosecha::class, 'acopio_cosecha_id');
    }
    
    // Relación anterior comentada
    // public function apiario()
    // {
    //     return $this->belongsTo(Apiario::class, 'apiario_id');
    // }
}
