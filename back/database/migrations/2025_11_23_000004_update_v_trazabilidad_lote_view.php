<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar vista si existe
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote');
        
        // Recrear vista con información de control de proceso
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id as lote_id,
                l.codigo_lote,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.cantidad_kg,
                l.tipo_envase,
                p.nombre_producto as producto,
                p.codigo_producto,
                t.nombre_tanque as tanque,
                ac.fecha_cosecha,
                ac.cantidad_kg as cantidad_cosecha_kg,
                CONCAT(prod.nombre, ' ', prod.apellidos) as productor,
                prod.runsa as registro_productor,
                a.nombre_cip as apiario,
                a.latitud as apiario_latitud,
                a.longitud as apiario_longitud,
                cp.id as control_proceso_id,
                cp.fecha_inicio as proceso_fecha_inicio,
                cp.fecha_fin as proceso_fecha_fin,
                cp.cantidad_entrada_kg as proceso_entrada_kg,
                cp.cantidad_salida_kg as proceso_salida_kg,
                cp.merma_kg as proceso_merma_kg,
                cp.merma_porcentaje as proceso_merma_porcentaje,
                cp.temperatura_proceso,
                cp.tiempo_proceso_horas,
                cp.metodo_proceso,
                cp.estado as proceso_estado
            FROM traza.lotes l
            INNER JOIN traza.productos p ON l.producto_id = p.id
            INNER JOIN traza.tanques t ON l.tanque_id = t.id
            INNER JOIN traza.acopio_cosechas ac ON l.cosecha_id = ac.id
            INNER JOIN traza.apiarios a ON ac.apiario_id = a.id
            INNER JOIN traza.productores prod ON a.productor_id = prod.id
            LEFT JOIN traza.control_procesos cp ON l.control_proceso_id = cp.id
            WHERE l.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS traza.v_trazabilidad_lote');
        
        // Recrear vista original sin información de proceso
        DB::statement("
            CREATE OR REPLACE VIEW traza.v_trazabilidad_lote AS
            SELECT 
                l.id as lote_id,
                l.codigo_lote,
                l.fecha_envasado,
                l.fecha_caducidad,
                l.cantidad_kg,
                l.tipo_envase,
                p.nombre_producto as producto,
                p.codigo_producto,
                t.nombre_tanque as tanque,
                ac.fecha_cosecha,
                ac.cantidad_kg as cantidad_cosecha_kg,
                CONCAT(prod.nombre, ' ', prod.apellidos) as productor,
                prod.runsa as registro_productor,
                a.nombre_cip as apiario,
                a.latitud as apiario_latitud,
                a.longitud as apiario_longitud
            FROM traza.lotes l
            INNER JOIN traza.productos p ON l.producto_id = p.id
            INNER JOIN traza.tanques t ON l.tanque_id = t.id
            INNER JOIN traza.acopio_cosechas ac ON l.cosecha_id = ac.id
            INNER JOIN traza.apiarios a ON ac.apiario_id = a.id
            INNER JOIN traza.productores prod ON a.productor_id = prod.id
            WHERE l.deleted_at IS NULL
        ");
    }
};
