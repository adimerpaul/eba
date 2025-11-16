<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Modelo para registro de limpieza y desinfección de equipos y herramientas apícolas
 * Relacionado con acopio_cosechas para generar formularios con datos de cabecera
 */
class Limpieza extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;
    
    // Campos actualizados según formulario físico
    protected $fillable = [
        'equipo_herramienta_material',
        'material_recubrimiento',
        'metodo_limpieza_utilizado',
        'producto_quimico_desinfeccion',
        'fecha_aplicacion',
        'acopio_cosecha_id',
    ];
    
    // Campos anteriores comentados para referencia
    // 'fecha_registro',
    // 'equipo',
    // 'material',
    // 'metodo_limpieza',
    // 'producto_limpieza',
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
