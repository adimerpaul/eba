<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use App\Models\Apiario;
use App\Models\AcopioCosecha;
use App\Models\Plaga;
use App\Models\Limpieza;
use App\Models\Medicamento;
use App\Models\AcopioTransporteLog;
use App\Models\ControlProceso;
use App\Models\Lote;
use App\Models\Tanque;
use App\Models\Kardex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardTrazabilidadController extends Controller
{
    /**
     * Estadísticas de capacidad productiva vs producción real
     * GET /dashboard-trazabilidad/capacidad-productiva?gestion=2025
     */
    public function capacidadProductiva(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $resumen = DB::selectOne("
            SELECT 
                COUNT(DISTINCT p.id) as total_productores,
                COALESCE(SUM(a.numero_colmenas_prod), 0) as total_colmenas,
                COALESCE(SUM(a.numero_colmenas_prod * 25), 0) as capacidad_teorica,
                COALESCE(SUM(ac.cantidad_kg), 0) as produccion_real
            FROM traza.productores p
            JOIN traza.apiarios a ON p.id = a.productor_id
            LEFT JOIN traza.acopio_cosechas ac ON a.id = ac.apiario_id 
                AND EXTRACT(YEAR FROM ac.fecha_cosecha) = ?
            WHERE a.estado = 'ACTIVO'
        ", [$gestion]);

        $capacidadTeorica = (float) $resumen->capacidad_teorica;
        $produccionReal = (float) $resumen->produccion_real;
        $eficiencia = $capacidadTeorica > 0 
            ? round(($produccionReal / $capacidadTeorica) * 100, 2) 
            : 0;
        $diferencia = $produccionReal - $capacidadTeorica;
        $restante = max(0, $capacidadTeorica - $produccionReal);

        $mesesRestantes = 12 - (int) now()->month;
        $metaMensual = $mesesRestantes > 0 ? round($restante / $mesesRestantes, 2) : 0;

        return response()->json([
            'gestion' => $gestion,
            'resumen' => [
                'total_productores' => (int) $resumen->total_productores,
                'total_colmenas' => (int) $resumen->total_colmenas,
                'capacidad_teorica' => round($capacidadTeorica, 2),
                'produccion_real' => round($produccionReal, 2),
                'eficiencia' => $eficiencia,
                'diferencia' => round($diferencia, 2),
                'produccion_esperada_restante' => round($restante, 2),
                'meses_restantes' => $mesesRestantes,
                'meta_mensual' => $metaMensual
            ]
        ]);
    }

    /**
     * Alertas de productores con sobreproducción
     * GET /dashboard-trazabilidad/alertas-sobreproduccion?gestion=2025&limit=10
     */
    public function alertasSobreproduccion(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);
        $limit = min((int) $request->input('limit', 10), 50);

        $alertas = DB::select("
            SELECT 
                p.id,
                CONCAT(p.nombre, ' ', COALESCE(p.apellidos, '')) as productor,
                p.runsa,
                COALESCE(SUM(a.numero_colmenas_prod * 25), 0) as capacidad_teorica,
                COALESCE(SUM(ac.cantidad_kg), 0) as produccion_real,
                COALESCE(SUM(ac.cantidad_kg), 0) - COALESCE(SUM(a.numero_colmenas_prod * 25), 0) as diferencia,
                ROUND((COALESCE(SUM(ac.cantidad_kg), 0) / NULLIF(SUM(a.numero_colmenas_prod * 25), 0)) * 100, 2) as porcentaje_sobre_capacidad
            FROM traza.productores p
            JOIN traza.apiarios a ON p.id = a.productor_id
            LEFT JOIN traza.acopio_cosechas ac ON a.id = ac.apiario_id 
                AND EXTRACT(YEAR FROM ac.fecha_cosecha) = ?
                AND ac.deleted_at IS NULL
            WHERE a.estado = 'ACTIVO'
                AND p.deleted_at IS NULL
                AND a.deleted_at IS NULL
            GROUP BY p.id, p.nombre, p.apellidos, p.runsa
            HAVING COALESCE(SUM(ac.cantidad_kg), 0) > COALESCE(SUM(a.numero_colmenas_prod * 25), 0)
            ORDER BY diferencia DESC
            LIMIT ?
        ", [$gestion, $limit]);

        return response()->json([
            'gestion' => $gestion,
            'total_alertas' => count($alertas),
            'alertas' => array_map(function($a) {
                return [
                    'productor_id' => $a->id,
                    'productor' => $a->productor,
                    'runsa' => $a->runsa,
                    'capacidad_teorica' => round((float) $a->capacidad_teorica, 2),
                    'produccion_real' => round((float) $a->produccion_real, 2),
                    'diferencia' => round((float) $a->diferencia, 2),
                    'porcentaje_sobre_capacidad' => (float) $a->porcentaje_sobre_capacidad
                ];
            }, $alertas)
        ]);
    }

    /**
     * Estadísticas de formularios SENASAG (plagas, medicamentos, limpiezas)
     * GET /dashboard-trazabilidad/senasag?gestion=2025
     */
    public function estadisticasSenasag(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $totalColmenas = Apiario::where('estado', 'ACTIVO')
            ->sum('numero_colmenas_prod');

        $plagas = DB::select("
            SELECT 
                nombre_plaga,
                COUNT(*) as total_casos,
                SUM(CASE 
                    WHEN plaga_presente = 'SI' AND numero_colmenas_apiario ~ '^[0-9]+$' 
                    THEN CAST(numero_colmenas_apiario AS INTEGER) 
                    ELSE 0 
                END) as colmenas_afectadas
            FROM traza.plagas
            WHERE EXTRACT(YEAR FROM fecha) = ?
                AND deleted_at IS NULL
            GROUP BY nombre_plaga
            ORDER BY total_casos DESC
            LIMIT 5
        ", [$gestion]);

        $medicamentos = DB::select("
            SELECT 
                nombre_producto,
                principio_activo,
                COUNT(*) as aplicaciones
            FROM traza.medicamentos
            WHERE EXTRACT(YEAR FROM fecha) = ?
                AND deleted_at IS NULL
            GROUP BY nombre_producto, principio_activo
            ORDER BY aplicaciones DESC
            LIMIT 5
        ", [$gestion]);

        $limpiezasCount = Limpieza::whereYear('fecha_aplicacion', $gestion)->count();

        $plagasArray = json_decode(json_encode($plagas), true);
        $totalCasosPlaga = array_sum(array_column($plagasArray, 'total_casos'));
        $totalColmenasAfectadas = array_sum(array_column($plagasArray, 'colmenas_afectadas'));
        $porcentajeAfectacion = $totalColmenas > 0 
            ? round(($totalColmenasAfectadas / $totalColmenas) * 100, 2) 
            : 0;

        return response()->json([
            'gestion' => $gestion,
            'plagas' => [
                'total_registros' => $totalCasosPlaga,
                'colmenas_afectadas' => $totalColmenasAfectadas,
                'total_colmenas' => $totalColmenas,
                'porcentaje_afectacion' => $porcentajeAfectacion,
                'mas_comunes' => array_map(function($p) {
                    return [
                        'nombre' => $p->nombre_plaga,
                        'casos' => (int) $p->total_casos,
                        'colmenas_afectadas' => (int) $p->colmenas_afectadas
                    ];
                }, $plagas)
            ],
            'medicamentos' => [
                'total_registros' => Medicamento::whereYear('fecha', $gestion)->count(),
                'mas_usados' => array_map(function($m) {
                    return [
                        'producto' => $m->nombre_producto,
                        'principio_activo' => $m->principio_activo,
                        'aplicaciones' => (int) $m->aplicaciones
                    ];
                }, $medicamentos)
            ],
            'limpiezas' => [
                'total_registros' => $limpiezasCount
            ]
        ]);
    }

    /**
     * Estadísticas de transporte desde apiarios
     * GET /dashboard-trazabilidad/transporte-apiarios?gestion=2025&limit=20
     */
    public function transporteApiarios(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);
        $limit = min((int) $request->input('limit', 20), 100);

        $transportes = DB::select("
            SELECT 
                atl.fecha_hora_salida,
                a.nombre_cip as nombre_apiario,
                ac.cantidad_kg,
                atl.temperatura_salida,
                COALESCE(atl.condiciones_envase, 'SIN_REGISTRO') as estado_carga,
                CASE 
                    WHEN atl.temperatura_salida > 30 THEN 'ALERTA_ALTA'
                    WHEN atl.temperatura_salida < 15 THEN 'ALERTA_BAJA'
                    ELSE 'CONFORME'
                END as estado_temperatura
            FROM traza.acopio_transporte_log atl
            JOIN traza.acopio_cosechas ac ON atl.acopio_cosecha_id = ac.id
            JOIN traza.apiarios a ON ac.apiario_id = a.id
            WHERE EXTRACT(YEAR FROM atl.fecha_hora_salida) = ?
                AND atl.deleted_at IS NULL
            ORDER BY atl.fecha_hora_salida DESC
            LIMIT ?
        ", [$gestion, $limit]);

        $resumen = DB::selectOne("
            SELECT 
                COUNT(*) as total_transportes,
                AVG(temperatura_salida) as temperatura_promedio,
                SUM(CASE WHEN temperatura_salida > 30 OR temperatura_salida < 15 THEN 1 ELSE 0 END) as fuera_rango
            FROM traza.acopio_transporte_log
            WHERE EXTRACT(YEAR FROM fecha_hora_salida) = ?
                AND deleted_at IS NULL
        ", [$gestion]);

        $totalTransportes = (int) ($resumen->total_transportes ?? 0);
        $fueraRango = (int) ($resumen->fuera_rango ?? 0);
        $temperaturaPromedio = $resumen->temperatura_promedio !== null ? (float) $resumen->temperatura_promedio : 0;
        $porcentajeFueraRango = $totalTransportes > 0 
            ? round(($fueraRango / $totalTransportes) * 100, 2) 
            : 0;

        return response()->json([
            'gestion' => $gestion,
            'resumen' => [
                'total_transportes' => $totalTransportes,
                'temperatura_promedio' => round($temperaturaPromedio, 2),
                'transportes_fuera_rango' => $fueraRango,
                'porcentaje_fuera_rango' => $porcentajeFueraRango
            ],
            'ultimos_transportes' => array_map(function($t) {
                return [
                    'fecha' => $t->fecha_hora_salida,
                    'apiario' => $t->nombre_apiario,
                    'cantidad_kg' => round((float) $t->cantidad_kg, 2),
                    'temperatura' => round((float) $t->temperatura_salida, 2),
                    'estado_carga' => $t->estado_carga,
                    'estado_temperatura' => $t->estado_temperatura
                ];
            }, $transportes)
        ]);
    }

    /**
     * Estadísticas de recepción y rechazos
     * GET /dashboard-trazabilidad/recepcion-rechazos?gestion=2025
     */
    public function recepcionRechazos(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $resumen = DB::selectOne("
            SELECT 
                COUNT(*) as total_acopios,
                SUM(cantidad_kg) as total_kg,
                SUM(CASE WHEN COALESCE(estado, 'PENDIENTE') != 'RECHAZADO' THEN 1 ELSE 0 END) as validados,
                SUM(CASE WHEN estado = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados,
                SUM(CASE WHEN COALESCE(estado, 'PENDIENTE') != 'RECHAZADO' THEN cantidad_kg ELSE 0 END) as cantidad_validada,
                SUM(CASE WHEN estado = 'RECHAZADO' THEN cantidad_kg ELSE 0 END) as cantidad_rechazada
            FROM traza.acopio_cosechas
            WHERE EXTRACT(YEAR FROM fecha_cosecha) = ?
                AND deleted_at IS NULL
        ", [$gestion]);

        $motivosRechazo = DB::select("
            SELECT 
                COALESCE(observaciones, 'SIN_ESPECIFICAR') as motivo_rechazo,
                COUNT(*) as total
            FROM traza.acopio_cosechas
            WHERE estado = 'RECHAZADO' 
                AND EXTRACT(YEAR FROM fecha_cosecha) = ?
                AND deleted_at IS NULL
            GROUP BY observaciones
            ORDER BY total DESC
        ", [$gestion]);

        $evolucionMensual = DB::select("
            SELECT 
                TO_CHAR(fecha_cosecha, 'YYYY-MM') as mes,
                COUNT(*) as total_acopios,
                SUM(cantidad_kg) as total_kg,
                SUM(CASE WHEN estado = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados
            FROM traza.acopio_cosechas
            WHERE EXTRACT(YEAR FROM fecha_cosecha) = ?
                AND deleted_at IS NULL
            GROUP BY TO_CHAR(fecha_cosecha, 'YYYY-MM')
            ORDER BY mes ASC
        ", [$gestion]);

        $totalAcopios = (int) ($resumen->total_acopios ?? 0);
        $validados = (int) ($resumen->validados ?? 0);
        $rechazados = (int) ($resumen->rechazados ?? 0);
        $cantidadValidada = (float) ($resumen->cantidad_validada ?? 0);
        $cantidadRechazada = (float) ($resumen->cantidad_rechazada ?? 0);
        $totalKg = (float) ($resumen->total_kg ?? 0);

        return response()->json([
            'gestion' => $gestion,
            'resumen' => [
                'total_acopios' => $totalAcopios,
                'cantidad_total' => round($totalKg, 2),
                'acopios_validados' => $validados,
                'cantidad_validada' => round($cantidadValidada, 2),
                'acopios_rechazados' => $rechazados,
                'cantidad_rechazada' => round($cantidadRechazada, 2),
                'tasa_validacion' => $totalAcopios > 0 ? round(($validados / $totalAcopios) * 100, 2) : 0,
                'porcentaje_rechazo' => $totalKg > 0 ? round(($cantidadRechazada / $totalKg) * 100, 2) : 0
            ],
            'motivos_rechazo' => count($motivosRechazo) > 0 ? array_map(function($m) {
                return [
                    'motivo' => $m->motivo_rechazo ?? 'Sin especificar',
                    'casos' => (int) $m->total,
                    'cantidad_kg' => 0,
                    'porcentaje' => 0
                ];
            }, $motivosRechazo) : [],
            'evolucion_mensual' => count($evolucionMensual) > 0 ? array_map(function($e) {
                $mes = explode('-', $e->mes)[1];
                return [
                    'mes' => (int) $mes,
                    'validados' => (int) $e->total_acopios - (int) $e->rechazados,
                    'rechazados' => (int) $e->rechazados
                ];
            }, $evolucionMensual) : []
        ]);
    }

    /**
     * Estadísticas de procesamiento y merma
     * GET /dashboard-trazabilidad/procesamiento-merma?gestion=2025
     */
    public function procesamientoMerma(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $resumen = DB::selectOne("
            SELECT 
                COUNT(*) as procesos_finalizados,
                SUM(cantidad_entrada_kg) as entrada_total,
                SUM(cantidad_salida_kg) as salida_total,
                SUM(merma_kg) as merma_total,
                AVG(merma_porcentaje) as merma_promedio
            FROM traza.control_procesos
            WHERE estado = 'FINALIZADO' 
                AND EXTRACT(YEAR FROM fecha_inicio) = ?
                AND deleted_at IS NULL
        ", [$gestion]);

        $eficienciaPorTanque = DB::select("
            SELECT 
                t.nombre_tanque,
                COUNT(cp.id) as procesos,
                COALESCE(SUM(cp.cantidad_entrada_kg), 0) as entrada_kg,
                COALESCE(SUM(cp.cantidad_salida_kg), 0) as salida_kg,
                AVG(cp.merma_porcentaje) as merma_promedio
            FROM traza.control_procesos cp
            JOIN traza.tanques t ON cp.tanque_id = t.id
            WHERE cp.estado = 'FINALIZADO' 
                AND EXTRACT(YEAR FROM cp.fecha_inicio) = ?
                AND cp.deleted_at IS NULL
                AND t.deleted_at IS NULL
            GROUP BY t.id, t.nombre_tanque
            ORDER BY merma_promedio ASC
        ", [$gestion]);

        $distribucionMerma = DB::select("
            SELECT 
                CASE 
                    WHEN merma_porcentaje BETWEEN 0 AND 3 THEN '0-3%'
                    WHEN merma_porcentaje BETWEEN 3.01 AND 5 THEN '3-5%'
                    WHEN merma_porcentaje BETWEEN 5.01 AND 7 THEN '5-7%'
                    ELSE '>7%'
                END as rango_merma,
                COUNT(*) as procesos
            FROM traza.control_procesos
            WHERE estado = 'FINALIZADO' 
                AND EXTRACT(YEAR FROM fecha_inicio) = ?
                AND deleted_at IS NULL
            GROUP BY rango_merma
            ORDER BY rango_merma
        ", [$gestion]);

        return response()->json([
            'gestion' => $gestion,
            'resumen' => [
                'procesos_finalizados' => (int) ($resumen->procesos_finalizados ?? 0),
                'entrada_total' => round((float) ($resumen->entrada_total ?? 0), 2),
                'salida_total' => round((float) ($resumen->salida_total ?? 0), 2),
                'merma_total' => round((float) ($resumen->merma_total ?? 0), 2),
                'merma_promedio' => round((float) ($resumen->merma_promedio ?? 0), 2)
            ],
            'eficiencia_tanques' => array_map(function($t) {
                return [
                    'tanque' => $t->nombre_tanque,
                    'procesos' => (int) $t->procesos,
                    'entrada_kg' => round((float) $t->entrada_kg, 2),
                    'salida_kg' => round((float) $t->salida_kg, 2),
                    'merma_porcentaje' => round((float) $t->merma_promedio, 2)
                ];
            }, $eficienciaPorTanque),
            'distribucion_merma' => array_map(function($d) {
                return [
                    'rango' => $d->rango_merma,
                    'procesos' => (int) $d->procesos
                ];
            }, $distribucionMerma)
        ]);
    }

    /**
     * Estadísticas de lotes y almacenamiento
     * GET /dashboard-trazabilidad/lotes-almacenamiento?gestion=2025
     */
    public function lotesAlmacenamiento(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $resumenLotes = DB::selectOne("
            SELECT 
                COUNT(DISTINCT l.id) as lotes_generados,
                SUM(l.cantidad_kg) as total_envasado,
                SUM(l.cantidad_kg) - COALESCE(SUM(k.salidas), 0) as stock_actual
            FROM traza.lotes l
            LEFT JOIN (
                SELECT lote_id, SUM(cantidad_salida) as salidas
                FROM traza.kardex
                WHERE cantidad_salida IS NOT NULL AND cantidad_salida > 0 AND deleted_at IS NULL
                GROUP BY lote_id
            ) k ON l.id = k.lote_id
            WHERE EXTRACT(YEAR FROM l.fecha_envasado) = ?
                AND l.deleted_at IS NULL
        ", [$gestion]);

        $lotesVendidos = DB::selectOne("
            SELECT COUNT(DISTINCT lote_id) as total
            FROM traza.kardex
            WHERE venta_id IS NOT NULL
                AND lote_id IN (
                    SELECT id FROM traza.lotes 
                    WHERE EXTRACT(YEAR FROM fecha_envasado) = ?
                        AND deleted_at IS NULL
                )
        ", [$gestion]);

        $tanques = DB::select("
            SELECT 
                t.nombre_tanque,
                t.capacidad_kg,
                COALESCE(SUM(l.cantidad_kg), 0) as ocupado_kg,
                ROUND((COALESCE(SUM(l.cantidad_kg), 0) / NULLIF(t.capacidad_kg, 0)) * 100, 2) as porcentaje_ocupacion
            FROM traza.tanques t
            LEFT JOIN traza.lotes l ON t.id = l.tanque_id 
                AND l.id NOT IN (SELECT DISTINCT lote_id FROM traza.kardex WHERE venta_id IS NOT NULL)
                AND l.deleted_at IS NULL
            WHERE t.estado_operativo = 'OPERATIVO'
            GROUP BY t.id, t.nombre_tanque, t.capacidad_kg
        ");

        $tanquesCriticos = array_filter($tanques, function($t) {
            return (float) $t->porcentaje_ocupacion > 80;
        });

        $capacidadTotal = count($tanques) > 0 ? array_sum(array_map(function($t) { return (float) $t->capacidad_kg; }, $tanques)) : 0;
        $ocupadoTotal = count($tanques) > 0 ? array_sum(array_map(function($t) { return (float) $t->ocupado_kg; }, $tanques)) : 0;

        $topProductos = DB::select("
            SELECT 
                p.nombre_producto,
                SUM(l.cantidad_kg) as total_kg
            FROM traza.lotes l
            JOIN traza.productos p ON l.producto_id = p.id
            WHERE EXTRACT(YEAR FROM l.fecha_envasado) = ?
                AND l.deleted_at IS NULL
                AND p.deleted_at IS NULL
            GROUP BY p.id, p.nombre_producto
            ORDER BY total_kg DESC
            LIMIT 5
        ", [$gestion]);

        $stockActual = (float) ($resumenLotes->stock_actual ?? 0);
        $totalEnvasado = (float) ($resumenLotes->total_envasado ?? 0);
        $porcentajeStock = $totalEnvasado > 0 ? round(($stockActual / $totalEnvasado) * 100, 2) : 0;

        $tanquesCriticosCount = count($tanquesCriticos);
        $porcentajeOcupacionTotal = $capacidadTotal > 0 ? round(($ocupadoTotal / $capacidadTotal) * 100, 2) : 0;

        return response()->json([
            'gestion' => $gestion,
            'resumen' => [
                'lotes_generados' => (int) ($resumenLotes->lotes_generados ?? 0),
                'stock_actual' => round($stockActual, 2),
                'ocupacion_tanques' => $porcentajeOcupacionTotal,
                'tanques_criticos' => $tanquesCriticosCount
            ],
            'detalle_tanques' => array_map(function($t) {
                return [
                    'tanque' => $t->nombre_tanque,
                    'tipo' => 'Almacenamiento',
                    'capacidad_kg' => round((float) $t->capacidad_kg, 2),
                    'stock_actual' => round((float) $t->ocupado_kg, 2),
                    'ocupacion_porcentaje' => round((float) $t->porcentaje_ocupacion, 2)
                ];
            }, $tanques)
        ]);
    }

    /**
     * Análisis de métodos de procesamiento
     * GET /dashboard-trazabilidad/metodos-procesamiento?gestion=2025
     */
    public function metodosProcessamiento(Request $request)
    {
        $gestion = $request->input('gestion', now()->year);

        $metodos = DB::select("
            SELECT 
                COALESCE(metodo_proceso, 'Sin especificar') as metodo,
                COUNT(*) as procesos,
                AVG(100 - merma_porcentaje) as eficiencia_promedio,
                AVG(temperatura_proceso) as temperatura_promedio,
                AVG(tiempo_proceso_horas) as tiempo_promedio
            FROM traza.control_procesos
            WHERE estado = 'FINALIZADO' 
                AND EXTRACT(YEAR FROM fecha_inicio) = ?
                AND deleted_at IS NULL
            GROUP BY metodo_proceso
            ORDER BY eficiencia_promedio DESC
        ", [$gestion]);

        return response()->json([
            'gestion' => $gestion,
            'metodos_usados' => array_map(function($m) {
                return [
                    'metodo' => $m->metodo,
                    'usos' => (int) $m->procesos,
                    'eficiencia_promedio' => round((float) $m->eficiencia_promedio, 2),
                    'temperatura_promedio' => round((float) $m->temperatura_promedio, 1),
                    'tiempo_promedio' => round((float) $m->tiempo_promedio, 1)
                ];
            }, $metodos)
        ]);
    }

    /**
     * Placeholder para estadísticas de mortandad (futuro)
     * GET /dashboard-trazabilidad/mortandad?gestion=2025
     */
    public function mortandad(Request $request)
    {
        return response()->json([
            'message' => 'Funcionalidad pendiente de implementacion',
            'descripcion' => 'Requiere formulario de captura de mortandad de colmenas',
            'datos_requeridos' => [
                'apiario_id',
                'fecha',
                'numero_colmenas_muertas',
                'causa_muerte',
                'causa_especifica',
                'perdida_estimada_kg',
                'observaciones',
                'accion_tomada'
            ],
            'tabla_propuesta' => 'mortandad_colmenas'
        ], 501);
    }
}

