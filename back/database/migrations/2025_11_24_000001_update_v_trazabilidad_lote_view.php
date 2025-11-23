<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Eliminar vista existente si existe
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote');

        // Recrear vista con información de control de proceso
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id AS lote_id,
                l.codigo_lote,
                l.cantidad_kg AS lote_cantidad_kg,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.tipo_envase,
                l.qr_code,
                
                -- Información del producto
                p.nombre_producto,
                p.codigo_producto,
                p.tipo_producto,
                
                -- Información del tanque
                t.nombre_tanque,
                t.capacidad_litros AS tanque_capacidad,
                
                -- Información de la cosecha
                c.fecha_cosecha,
                c.cantidad_kg AS cosecha_cantidad_kg,
                c.calidad AS cosecha_calidad,
                c.temperatura AS cosecha_temperatura,
                c.humedad AS cosecha_humedad,
                
                -- Información del proceso (nuevo)
                cp.id AS proceso_id,
                cp.fecha_inicio AS proceso_fecha_inicio,
                cp.fecha_fin AS proceso_fecha_fin,
                cp.cantidad_entrada_kg AS proceso_entrada_kg,
                cp.cantidad_salida_kg AS proceso_salida_kg,
                cp.merma_kg AS proceso_merma_kg,
                cp.merma_porcentaje AS proceso_merma_porcentaje,
                cp.estado AS proceso_estado,
                cp.temperatura_proceso,
                cp.tiempo_proceso_horas,
                cp.metodo_proceso,
                
                -- Información del productor
                pr.nombres AS productor_nombres,
                pr.apellidos AS productor_apellidos,
                pr.ci AS productor_ci,
                pr.telefono AS productor_telefono,
                
                -- Información del apiario
                a.nombre_apiario,
                a.coordenadas AS apiario_coordenadas,
                a.altitud AS apiario_altitud,
                a.tipo_manejo,
                
                -- Información de la organización
                o.nombre_organizacion,
                o.sigla AS organizacion_sigla,
                
                -- Información geográfica
                m.nombre AS municipio,
                pv.nombre AS provincia,
                d.nombre AS departamento,
                
                -- Timestamps
                l.created_at AS lote_created_at,
                l.updated_at AS lote_updated_at
                
            FROM traza.lotes l
            LEFT JOIN traza.productos p ON l.producto_id = p.id
            LEFT JOIN traza.tanques t ON l.tanque_id = t.id
            LEFT JOIN traza.acopio_cosechas c ON l.cosecha_id = c.id
            LEFT JOIN traza.control_procesos cp ON l.control_proceso_id = cp.id
            LEFT JOIN traza.productores pr ON c.productor_id = pr.id
            LEFT JOIN traza.apiarios a ON c.apiario_id = a.id
            LEFT JOIN traza.organizaciones o ON pr.organizacion_id = o.id
            LEFT JOIN public.municipios m ON pr.municipio_id = m.id
            LEFT JOIN public.provincias pv ON m.provincia_id = pv.id
            LEFT JOIN public.departamentos d ON pv.departamento_id = d.id
            WHERE l.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar vista
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote');
        
        // Recrear vista sin información de proceso (versión anterior)
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id AS lote_id,
                l.codigo_lote,
                l.cantidad_kg AS lote_cantidad_kg,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.tipo_envase,
                l.qr_code,
                p.nombre_producto,
                p.codigo_producto,
                p.tipo_producto,
                t.nombre_tanque,
                t.capacidad_litros AS tanque_capacidad,
                c.fecha_cosecha,
                c.cantidad_kg AS cosecha_cantidad_kg,
                c.calidad AS cosecha_calidad,
                c.temperatura AS cosecha_temperatura,
                c.humedad AS cosecha_humedad,
                pr.nombres AS productor_nombres,
                pr.apellidos AS productor_apellidos,
                pr.ci AS productor_ci,
                pr.telefono AS productor_telefono,
                a.nombre_apiario,
                a.coordenadas AS apiario_coordenadas,
                a.altitud AS apiario_altitud,
                a.tipo_manejo,
                o.nombre_organizacion,
                o.sigla AS organizacion_sigla,
                m.nombre AS municipio,
                pv.nombre AS provincia,
                d.nombre AS departamento,
                l.created_at AS lote_created_at,
                l.updated_at AS lote_updated_at
            FROM traza.lotes l
            LEFT JOIN traza.productos p ON l.producto_id = p.id
            LEFT JOIN traza.tanques t ON l.tanque_id = t.id
            LEFT JOIN traza.acopio_cosechas c ON l.cosecha_id = c.id
            LEFT JOIN traza.productores pr ON c.productor_id = pr.id
            LEFT JOIN traza.apiarios a ON c.apiario_id = a.id
            LEFT JOIN traza.organizaciones o ON pr.organizacion_id = o.id
            LEFT JOIN public.municipios m ON pr.municipio_id = m.id
            LEFT JOIN public.provincias pv ON m.provincia_id = pv.id
            LEFT JOIN public.departamentos d ON pv.departamento_id = d.id
            WHERE l.deleted_at IS NULL
        ");
    }
};
