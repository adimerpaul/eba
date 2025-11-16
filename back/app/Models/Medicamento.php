<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo para registro de aplicación de medicamentos
 * Relacionado con acopio_cosechas para generar formularios con datos de cabecera
 */
class Medicamento extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    // Campos actualizados según formulario físico
    protected $fillable = [
        'fecha',
        'nombre_producto',
        'principio_activo',
        'dosis_recomendada',
        'dosis_aplicada',
        'plagas_controladas',
        'periodo_espera_cosecha',
        'nombre_encargado',
        'firma',
        'acopio_cosecha_id',
    ];
    
    // Campos anteriores comentados para referencia
    // 'fecha_registro',
    // 'activo',
    // 'recomendada',
    // 'aplicada',
    // 'plagas',
    // 'periodo',
    // 'encargado',
    // 'apiario_id',
    
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
