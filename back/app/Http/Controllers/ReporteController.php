<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    //
    public function reporteAcopioProveedorDep(Request $request){
        // Lógica para generar el reporte de productos
        $res = DB::SELECT("SELECT 
            d.nombre_departamento,
            COUNT(DISTINCT p.id) AS productores,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 1) AS enero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 2) AS febrero,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 3) AS marzo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 4) AS abril,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 5) AS mayo,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 6) AS junio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 7) AS julio,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 8) AS agosto,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 9) AS septiembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 10) AS octubre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 11) AS noviembre,
            SUM(ac.cantidad_kg) FILTER (WHERE EXTRACT(MONTH FROM ac.fecha_cosecha) = 12) AS diciembre,
            SUM(ac.cantidad_kg) AS total_anual
            FROM departamentos d 
            INNER JOIN municipios m ON d.id = m.departamento_id
            INNER JOIN productores p ON p.municipio_id = m.id 
            INNER JOIN apiarios a ON a.productor_id = p.id
            INNER JOIN acopio_cosechas ac ON ac.apiario_id = a.id
            WHERE ac.producto_id = $request->producto_id
            AND ac.fecha_cosecha BETWEEN '$request->inicio' AND '$request->fin'
            GROUP BY d.nombre_departamento
            ORDER BY d.nombre_departamento;");
        return $res;
    }

    public function reportePorcentual(Request $request){
        // Lógica para generar el reporte de productos
        $request->validate([
            'producto_id' => 'required|integer',
            'inicio' => 'required|date',
            'fin' => 'required|date',
        ]);

        // Ejecutamos la consulta
        $res = DB::select("
            WITH meses AS (
                SELECT generate_series(1, 12) AS mes
            ),
            acopio_mes AS (
                SELECT 
                    EXTRACT(MONTH FROM ac.fecha_cosecha)::int AS mes,
                    SUM(ac.cantidad_kg) AS total_mes,
                    COUNT(DISTINCT p.id) AS productores_mes
                FROM departamentos d
                INNER JOIN municipios m ON d.id = m.departamento_id
                INNER JOIN productores p ON p.municipio_id = m.id 
                INNER JOIN apiarios a ON a.productor_id = p.id
                INNER JOIN acopio_cosechas ac ON ac.apiario_id = a.id
                WHERE ac.producto_id = :producto_id
                    AND ac.fecha_cosecha BETWEEN :inicio AND :fin
                GROUP BY EXTRACT(MONTH FROM ac.fecha_cosecha)
            ),
            total_anual AS (
                SELECT COALESCE(SUM(total_mes), 0) AS total_kg_anual FROM acopio_mes
            )
            SELECT 
                m.mes,
                INITCAP(TO_CHAR(TO_DATE(m.mes::text, 'MM'), 'TMMonth')) AS nombre_mes,
                COALESCE(a.productores_mes, 0) AS productores_mes,
                COALESCE(a.total_mes, 0) AS acopio_kg,
                CASE 
                    WHEN t.total_kg_anual > 0 
                    THEN ROUND((COALESCE(a.total_mes, 0) / t.total_kg_anual) * 100, 2)
                    ELSE 0
                END AS porcentaje
            FROM meses m
            LEFT JOIN acopio_mes a ON m.mes = a.mes
            CROSS JOIN total_anual t
            ORDER BY m.mes;
        ", [
            'producto_id' => $request->producto_id,
            'inicio' => $request->inicio,
            'fin' => $request->fin,
        ]);

        // Estructuramos los datos para el front
        $labels = [];
        $porcentajes = [];
        $productores = [];

        foreach ($res as $fila) {
            $labels[] = $fila->nombre_mes;
            $porcentajes[] = (float)$fila->porcentaje;
            $productores[] = (int)$fila->productores_mes;
        }

        return response()->json([
            'labels' => $labels,
            'porcentaje' => $porcentajes,
            'productores' => $productores,
        ]);
    }
}

