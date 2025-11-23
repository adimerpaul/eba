<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// Auditoría temporalmente deshabilitada para evitar conflictos de ID
// use OwenIt\Auditing\Contracts\Auditable;
// use OwenIt\Auditing\Auditable as AuditableTrait;

class AcopioRechazo extends Model // implements Auditable
{
    use SoftDeletes; // , AuditableTrait;

    protected $table = 'acopio_rechazos';

    protected $fillable = [
        'acopio_cosecha_id',
        'motivo_rechazo',
        'observaciones',
        'accion_correctiva',
        'rechazado_por',
        'estado_devolucion',
        'fecha_rechazo',
        'fecha_notificacion',
        'fecha_devolucion',
        'devuelto_por',
        'evidencias',
        'procesamiento_masivo_log_id',
    ];

    protected $casts = [
        'evidencias' => 'array',
        'fecha_rechazo' => 'datetime',
        'fecha_notificacion' => 'datetime',
        'fecha_devolucion' => 'datetime',
    ];

    /**
     * Relación con el acopio rechazado
     */
    public function acopioCosecha(): BelongsTo
    {
        return $this->belongsTo(AcopioCosecha::class, 'acopio_cosecha_id')
            ->with(['apiario.productor', 'producto']);
    }

    /**
     * Usuario que rechazó el acopio
     */
    public function rechazadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rechazado_por');
    }

    /**
     * Usuario que gestionó la devolución
     */
    public function devueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devuelto_por');
    }

    /**
     * Log de procesamiento masivo relacionado
     */
    public function procesamientoLog(): BelongsTo
    {
        return $this->belongsTo(ProcesamientoMasivoLog::class, 'procesamiento_masivo_log_id');
    }

    /**
     * Accessor: Obtener label legible del motivo
     */
    public function getMotivoLabelAttribute(): string
    {
        $labels = [
            'CALIDAD_INSUFICIENTE' => 'Calidad Insuficiente',
            'HUMEDAD_ALTA' => 'Humedad Alta',
            'CONTAMINACION' => 'Contaminación',
            'DOCUMENTACION_INCOMPLETA' => 'Documentación Incompleta',
            'TEMPERATURA_INCORRECTA' => 'Temperatura Incorrecta',
            'PESO_INCORRECTO' => 'Peso Incorrecto',
            'ENVASE_INADECUADO' => 'Envase Inadecuado',
            'OTRO' => 'Otro',
        ];

        return $labels[$this->motivo_rechazo] ?? $this->motivo_rechazo;
    }

    /**
     * Accessor: Color del chip según estado
     */
    public function getEstadoColorAttribute(): string
    {
        $colores = [
            'PENDIENTE' => 'red',
            'NOTIFICADO' => 'orange',
            'DEVUELTO' => 'grey',
            'CANCELADO' => 'black',
        ];

        return $colores[$this->estado_devolucion] ?? 'grey';
    }

    /**
     * Accessor: Días desde el rechazo
     */
    public function getDiasDesdeRechazoAttribute(): int
    {
        return $this->fecha_rechazo->diffInDays(now());
    }

    /**
     * Scope: Rechazos pendientes de devolución
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_devolucion', 'PENDIENTE');
    }

    /**
     * Scope: Rechazos ya devueltos
     */
    public function scopeDevueltos($query)
    {
        return $query->where('estado_devolucion', 'DEVUELTO');
    }

    /**
     * Scope: Filtrar por motivo
     */
    public function scopePorMotivo($query, string $motivo)
    {
        return $query->where('motivo_rechazo', $motivo);
    }

    /**
     * Scope: Filtrar por productor
     */
    public function scopePorProductor($query, int $productorId)
    {
        return $query->whereHas('acopioCosecha.apiario', function ($q) use ($productorId) {
            $q->where('productor_id', $productorId);
        });
    }

    /**
     * Marcar como devuelto
     */
    public function marcarComoDevuelto(int $usuarioId): bool
    {
        return $this->update([
            'estado_devolucion' => 'DEVUELTO',
            'fecha_devolucion' => now(),
            'devuelto_por' => $usuarioId,
        ]);
    }

    /**
     * Marcar como notificado
     */
    public function marcarComoNotificado(): bool
    {
        return $this->update([
            'estado_devolucion' => 'NOTIFICADO',
            'fecha_notificacion' => now(),
        ]);
    }
}
