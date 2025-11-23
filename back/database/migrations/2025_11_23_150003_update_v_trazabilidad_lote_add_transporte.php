<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Actualizar vista v_trazabilidad_lote para incluir información de transporte.
     * Agrega campos de control de transporte SENASAG para trazabilidad completa.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote CASCADE');
        
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id AS lote_id,
                l.codigo_lote,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.cantidad_kg,
                l.tipo_envase,
                p.nombre_producto AS producto,
                p.codigo_producto,
                t.nombre_tanque AS tanque,
                ac.fecha_cosecha,
                ac.cantidad_kg AS cantidad_cosecha_kg,
                CONCAT(prod.nombre, ' ', prod.apellidos) AS productor,
                prod.runsa AS registro_productor,
                a.nombre_cip AS apiario,
                a.latitud AS apiario_latitud,
                a.longitud AS apiario_longitud,
                cp.id AS control_proceso_id,
                cp.fecha_inicio AS proceso_fecha_inicio,
                cp.fecha_fin AS proceso_fecha_fin,
                cp.cantidad_entrada_kg AS proceso_entrada_kg,
                cp.cantidad_salida_kg AS proceso_salida_kg,
                cp.merma_kg AS proceso_merma_kg,
                cp.merma_porcentaje AS proceso_merma_porcentaje,
                cp.temperatura_proceso,
                cp.tiempo_proceso_horas,
                cp.metodo_proceso,
                cp.estado AS proceso_estado,
                
                -- Información de transporte de entrada (materia prima)
                atl.id AS transporte_log_id,
                trans.empresa AS transporte_empresa,
                trans.placa AS transporte_placa,
                trans.responsable AS transporte_conductor,
                atl.lugar_origen AS transporte_origen,
                atl.lugar_destino AS transporte_destino,
                atl.distancia_km AS transporte_distancia_km,
                atl.temperatura_salida AS transporte_temp_salida,
                atl.temperatura_llegada AS transporte_temp_llegada,
                atl.temperatura_maxima AS transporte_temp_maxima,
                atl.temperatura_minima AS transporte_temp_minima,
                atl.fecha_hora_salida AS transporte_fecha_salida,
                atl.fecha_hora_llegada AS transporte_fecha_llegada,
                atl.tiempo_transporte_horas AS transporte_duracion_horas,
                atl.condiciones_envase AS transporte_condiciones_envase,
                atl.condiciones_vehiculo AS transporte_condiciones_vehiculo,
                atl.alerta_temperatura AS transporte_alerta_temperatura,
                atl.alerta_tiempo AS transporte_alerta_tiempo,
                
                -- Estado de cumplimiento SENASAG
                CASE 
                    WHEN atl.alerta_temperatura = true OR atl.alerta_tiempo = true THEN 'NO_CONFORME'
                    WHEN atl.id IS NULL THEN 'SIN_REGISTRO_TRANSPORTE'
                    ELSE 'CONFORME'
                END AS transporte_estado_senasag
                
            FROM lotes l
            JOIN productos p ON l.producto_id = p.id
            JOIN tanques t ON l.tanque_id = t.id
            JOIN acopio_cosechas ac ON l.cosecha_id = ac.id
            JOIN apiarios a ON ac.apiario_id = a.id
            JOIN productores prod ON a.productor_id = prod.id
            LEFT JOIN control_procesos cp ON l.control_proceso_id = cp.id
            LEFT JOIN (
                -- Obtener el registro de transporte más reciente para cada acopio
                SELECT DISTINCT ON (acopio_cosecha_id) 
                    id,
                    acopio_cosecha_id,
                    transporte_id,
                    lugar_origen,
                    lugar_destino,
                    distancia_km,
                    temperatura_salida,
                    temperatura_llegada,
                    temperatura_maxima,
                    temperatura_minima,
                    fecha_hora_salida,
                    fecha_hora_llegada,
                    tiempo_transporte_horas,
                    condiciones_envase,
                    condiciones_vehiculo,
                    alerta_temperatura,
                    alerta_tiempo
                FROM acopio_transporte_log
                WHERE deleted_at IS NULL
                ORDER BY acopio_cosecha_id, fecha_hora_salida DESC
            ) atl ON atl.acopio_cosecha_id = ac.id
            LEFT JOIN transportes trans ON atl.transporte_id = trans.id
            WHERE l.deleted_at IS NULL
        ");
    }

    /**
     * Revertir a la vista anterior sin información de transporte
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote CASCADE');
        
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id AS lote_id,
                l.codigo_lote,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.cantidad_kg,
                l.tipo_envase,
                p.nombre_producto AS producto,
                p.codigo_producto,
                t.nombre_tanque AS tanque,
                ac.fecha_cosecha,
                ac.cantidad_kg AS cantidad_cosecha_kg,
                CONCAT(prod.nombre, ' ', prod.apellidos) AS productor,
                prod.runsa AS registro_productor,
                a.nombre_cip AS apiario,
                a.latitud AS apiario_latitud,
                a.longitud AS apiario_longitud,
                cp.id AS control_proceso_id,
                cp.fecha_inicio AS proceso_fecha_inicio,
                cp.fecha_fin AS proceso_fecha_fin,
                cp.cantidad_entrada_kg AS proceso_entrada_kg,
                cp.cantidad_salida_kg AS proceso_salida_kg,
                cp.merma_kg AS proceso_merma_kg,
                cp.merma_porcentaje AS proceso_merma_porcentaje,
                cp.temperatura_proceso,
                cp.tiempo_proceso_horas,
                cp.metodo_proceso,
                cp.estado AS proceso_estado
            FROM lotes l
            JOIN productos p ON l.producto_id = p.id
            JOIN tanques t ON l.tanque_id = t.id
            JOIN acopio_cosechas ac ON l.cosecha_id = ac.id
            JOIN apiarios a ON ac.apiario_id = a.id
            JOIN productores prod ON a.productor_id = prod.id
            LEFT JOIN control_procesos cp ON l.control_proceso_id = cp.id
            WHERE l.deleted_at IS NULL
        ");
    }
};
