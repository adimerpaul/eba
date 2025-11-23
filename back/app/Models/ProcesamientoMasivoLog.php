<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Auditoría temporalmente deshabilitada para evitar conflictos de ID
// use OwenIt\Auditing\Contracts\Auditable;
// use OwenIt\Auditing\Auditable as AuditableTrait;

class ProcesamientoMasivoLog extends Model // implements Auditable
{
    use SoftDeletes; // , AuditableTrait;

    protected $table = 'procesamiento_masivo_logs';

    protected $fillable = [
        'usuario_id',
        'tipo_procesamiento',
        'acopios_procesados',
        'acopios_rechazados',
        'acopios_fallidos',
        'total_kg_procesado',
        'total_costo',
        'filtros_aplicados',
        'acopio_ids',
        'observaciones',
        'fecha_ejecucion',
        'duracion_segundos',
    ];

    protected $casts = [
        'filtros_aplicados' => 'array',
        'acopio_ids' => 'array',
        'fecha_ejecucion' => 'datetime',
        'acopios_procesados' => 'integer',
        'acopios_rechazados' => 'integer',
        'acopios_fallidos' => 'integer',
        'total_kg_procesado' => 'decimal:2',
        'total_costo' => 'decimal:2',
        'duracion_segundos' => 'integer',
    ];

    /**
     * Relación con el usuario que ejecutó el procesamiento
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Obtener acopios procesados (relación manual mediante array de IDs)
     */
    public function acopios()
    {
        if (!$this->acopio_ids || !is_array($this->acopio_ids)) {
            return collect([]);
        }
        
        return AcopioCosecha::whereIn('id', $this->acopio_ids)->get();
    }

    /**
     * Accessor: Resumen legible del procesamiento
     */
    public function getResumenAttribute(): string
    {
        return sprintf(
            '%s - %d procesados, %d rechazados | %.2f kg | Bs. %.2f',
            $this->tipo_procesamiento,
            $this->acopios_procesados,
            $this->acopios_rechazados,
            $this->total_kg_procesado,
            $this->total_costo
        );
    }

    /**
     * Accessor: Filtros aplicados como string legible
     */
    public function getFiltrosTextoAttribute(): string
    {
        if (!$this->filtros_aplicados || !is_array($this->filtros_aplicados)) {
            return 'Sin filtros';
        }

        $filtros = [];
        foreach ($this->filtros_aplicados as $key => $value) {
            if ($value !== null && $value !== '') {
                $filtros[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $filtros) ?: 'Sin filtros';
    }

    /**
     * Scope: Filtrar por tipo de procesamiento
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo_procesamiento', $tipo);
    }

    /**
     * Scope: Filtrar por usuario
     */
    public function scopePorUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_ejecucion', [$desde, $hasta]);
    }
}
