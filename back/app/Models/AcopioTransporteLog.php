<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para control de transporte de acopios de cosecha.
 * Registra información detallada del transporte de materia prima desde apiarios.
 * Cumple con requisitos SENASAG para trazabilidad de cadena de frío.
 */
class AcopioTransporteLog extends Model
{
    use SoftDeletes;

    protected $table = 'acopio_transporte_log';

    protected $fillable = [
        'acopio_cosecha_id',
        'transporte_id',
        'lugar_origen',
        'lugar_destino',
        'distancia_km',
        'temperatura_salida',
        'temperatura_llegada',
        'temperatura_maxima',
        'temperatura_minima',
        'fecha_hora_salida',
        'fecha_hora_llegada',
        'tiempo_transporte_horas',
        'condiciones_envase',
        'condiciones_vehiculo',
        'observaciones',
        'alerta_temperatura',
        'alerta_tiempo',
        'registrado_por',
    ];

    protected $casts = [
        'distancia_km' => 'decimal:2',
        'temperatura_salida' => 'decimal:2',
        'temperatura_llegada' => 'decimal:2',
        'temperatura_maxima' => 'decimal:2',
        'temperatura_minima' => 'decimal:2',
        'fecha_hora_salida' => 'datetime',
        'fecha_hora_llegada' => 'datetime',
        'tiempo_transporte_horas' => 'decimal:2',
        'alerta_temperatura' => 'boolean',
        'alerta_tiempo' => 'boolean',
    ];

    /**
     * Relación con el acopio de cosecha
     */
    public function acopioCosecha(): BelongsTo
    {
        return $this->belongsTo(AcopioCosecha::class, 'acopio_cosecha_id');
    }

    /**
     * Relación con el transporte utilizado
     */
    public function transporte(): BelongsTo
    {
        return $this->belongsTo(Transporte::class, 'transporte_id');
    }

    /**
     * Relación con el usuario que registró el transporte
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Calcular duración del transporte en horas
     */
    public function calcularDuracionTransporte(): ?float
    {
        if ($this->fecha_hora_salida && $this->fecha_hora_llegada) {
            $diferencia = $this->fecha_hora_llegada->diffInMinutes($this->fecha_hora_salida);
            return round($diferencia / 60, 2);
        }
        return null;
    }

    /**
     * Verificar si excede temperatura máxima permitida
     * Límite SENASAG: máximo 35°C durante transporte
     */
    public function verificarAlertaTemperatura(): bool
    {
        $limiteTemperatura = 35.0;
        
        if ($this->temperatura_maxima && $this->temperatura_maxima > $limiteTemperatura) {
            return true;
        }
        
        if ($this->temperatura_llegada && $this->temperatura_llegada > $limiteTemperatura) {
            return true;
        }
        
        return false;
    }

    /**
     * Verificar si excede tiempo máximo permitido
     * Límite recomendado: máximo 6 horas de transporte
     */
    public function verificarAlertaTiempo(): bool
    {
        $limiteTiempo = 6.0;
        
        if ($this->tiempo_transporte_horas && $this->tiempo_transporte_horas > $limiteTiempo) {
            return true;
        }
        
        return false;
    }

    /**
     * Obtener variación de temperatura durante transporte
     */
    public function getVariacionTemperaturaAttribute(): ?float
    {
        if ($this->temperatura_salida && $this->temperatura_llegada) {
            return round(abs($this->temperatura_llegada - $this->temperatura_salida), 2);
        }
        return null;
    }

    /**
     * Obtener estado de cumplimiento SENASAG
     */
    public function getEstadoCumplimientoAttribute(): string
    {
        if ($this->alerta_temperatura || $this->alerta_tiempo) {
            return 'NO_CONFORME';
        }
        
        if ($this->temperatura_maxima === null && $this->tiempo_transporte_horas === null) {
            return 'SIN_DATOS';
        }
        
        return 'CONFORME';
    }

    /**
     * Scope: Filtrar por acopio
     */
    public function scopeDeAcopio($query, int $acopioId)
    {
        return $query->where('acopio_cosecha_id', $acopioId);
    }

    /**
     * Scope: Filtrar por transporte
     */
    public function scopeDeTransporte($query, int $transporteId)
    {
        return $query->where('transporte_id', $transporteId);
    }

    /**
     * Scope: Filtrar con alertas
     */
    public function scopeConAlertas($query)
    {
        return $query->where(function($q) {
            $q->where('alerta_temperatura', true)
              ->orWhere('alerta_tiempo', true);
        });
    }

    /**
     * Scope: Filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_hora_salida', [$desde, $hasta]);
    }

    /**
     * Boot del modelo para calcular automáticamente alertas
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($log) {
            // Calcular duración si hay fechas
            if ($log->fecha_hora_salida && $log->fecha_hora_llegada && !$log->tiempo_transporte_horas) {
                $log->tiempo_transporte_horas = $log->calcularDuracionTransporte();
            }
            
            // Verificar alertas automáticamente
            $log->alerta_temperatura = $log->verificarAlertaTemperatura();
            $log->alerta_tiempo = $log->verificarAlertaTiempo();
        });
    }
}
