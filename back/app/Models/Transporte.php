<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Transporte extends Model implements Auditable{
    use AuditableTrait, SoftDeletes;
//Schema::create('transportes', function (Blueprint $table) {
//    $table->id();
////            CREATE TABLE transportes (
////                id serial4 NOT NULL PRIMARY KEY,
////	empresa varchar(150) NULL,
////	placa varchar(20) NULL,
////	responsable varchar(150) NULL
////);
////COMMENT ON TABLE transportes IS 'Tabla registra los los transportes o empresas o conductores que llevaran la miel';
////COMMENT ON COLUMN transportes.id IS 'clave  ID clave unico de la tabla autoincrementable';
////COMMENT ON COLUMN transportes.empresa IS 'nombre o descripcion delconductor o empresa de transporte';
////COMMENT ON COLUMN transportes.placa IS 'numero de placa de la movilidad de transporte';
////COMMENT ON COLUMN transportes.responsable IS 'nombre o descripcion del conductor de transporte';
//    $table->string('empresa', 150)->nullable();
//    $table->string('placa', 20)->nullable();
//    $table->string('responsable', 150)->nullable();
//    $table->softDeletes();
//    $table->timestamps();
//});
    protected $fillable = [
        'empresa',
        'placa',
        'responsable',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relación con ventas (salida de producto terminado)
     */
    public function ventas()
    {
        return $this->hasMany(\App\Models\Venta::class, 'transporte_id');
    }

    /**
     * Relación con registros de transporte de acopios (entrada de materia prima)
     * Permite trazabilidad completa del transporte
     */
    public function acopioLogs()
    {
        return $this->hasMany(\App\Models\AcopioTransporteLog::class, 'transporte_id');
    }

    /**
     * Obtener estadísticas de uso del transporte
     */
    public function getEstadisticasUsoAttribute()
    {
        return [
            'total_acopios' => $this->acopioLogs()->count(),
            'total_ventas' => $this->ventas()->count(),
            'total_viajes' => $this->acopioLogs()->count() + $this->ventas()->count(),
            'alertas_temperatura' => $this->acopioLogs()->where('alerta_temperatura', true)->count(),
            'alertas_tiempo' => $this->acopioLogs()->where('alerta_tiempo', true)->count(),
        ];
    }

    /**
     * Obtener estadísticas completas separadas por tipo de viaje
     */
    public function getEstadisticasCompletasAttribute()
    {
        // Estadísticas de ENTRADA (Acopios)
        $entrada = [
            'total_viajes' => $this->acopioLogs()->count(),
            'kg_transportados' => $this->acopioLogs()
                ->join('acopio_cosechas', 'acopio_transporte_log.acopio_cosecha_id', '=', 'acopio_cosechas.id')
                ->sum('acopio_cosechas.cantidad_kg'),
            'distancia_total_km' => $this->acopioLogs()->sum('distancia_km'),
            'duracion_total_horas' => $this->acopioLogs()->sum('tiempo_transporte_horas'),
            'alertas_temperatura' => $this->acopioLogs()->where('alerta_temperatura', true)->count(),
            'alertas_tiempo' => $this->acopioLogs()->where('alerta_tiempo', true)->count(),
            'productores_atendidos' => $this->acopioLogs()
                ->join('acopio_cosechas', 'acopio_transporte_log.acopio_cosecha_id', '=', 'acopio_cosechas.id')
                ->join('apiarios', 'acopio_cosechas.apiario_id', '=', 'apiarios.id')
                ->distinct('apiarios.productor_id')
                ->count('apiarios.productor_id'),
        ];

        // Estadísticas de SALIDA (Ventas)
        $salida = [
            'total_entregas' => $this->ventas()->count(),
            'kg_entregados' => $this->ventas()
                ->join('detalle_ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
                ->sum('detalle_ventas.cantidad_salida'),
            'valor_total' => $this->ventas()->sum('precio_total'),
            'clientes_atendidos' => $this->ventas()->distinct('cliente_id')->count('cliente_id'),
        ];

        // Calcular promedios
        if ($entrada['total_viajes'] > 0) {
            $entrada['kg_promedio'] = round($entrada['kg_transportados'] / $entrada['total_viajes'], 2);
            $entrada['distancia_promedio_km'] = round($entrada['distancia_total_km'] / $entrada['total_viajes'], 2);
            $entrada['duracion_promedio_horas'] = round($entrada['duracion_total_horas'] / $entrada['total_viajes'], 2);
            $entrada['porcentaje_cumplimiento'] = round(
                (($entrada['total_viajes'] - $entrada['alertas_temperatura'] - $entrada['alertas_tiempo']) / $entrada['total_viajes']) * 100,
                1
            );
        } else {
            $entrada['kg_promedio'] = 0;
            $entrada['distancia_promedio_km'] = 0;
            $entrada['duracion_promedio_horas'] = 0;
            $entrada['porcentaje_cumplimiento'] = 100;
        }

        if ($salida['total_entregas'] > 0) {
            $salida['kg_promedio'] = round($salida['kg_entregados'] / $salida['total_entregas'], 2);
            $salida['valor_promedio'] = round($salida['valor_total'] / $salida['total_entregas'], 2);
        } else {
            $salida['kg_promedio'] = 0;
            $salida['valor_promedio'] = 0;
        }

        return [
            'entrada' => $entrada,
            'salida' => $salida,
            'totales' => [
                'total_viajes' => $entrada['total_viajes'] + $salida['total_entregas'],
                'total_kg' => $entrada['kg_transportados'] + $salida['kg_entregados'],
            ]
        ];
    }
}
