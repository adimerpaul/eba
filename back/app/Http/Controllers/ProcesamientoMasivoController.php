<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use App\Models\ProcesamientoMasivoLog;
use App\Models\AcopioRechazo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProcesamientoMasivoController extends Controller
{
    /**
     * Listar acopios disponibles para procesar con filtros avanzados
     * GET /procesamiento-masivo/acopios-disponibles
     */
    public function acopiosDisponibles(Request $request)
    {
        $query = AcopioCosecha::query()
            ->with(['apiario.productor.organizacion', 'producto'])
            ->where('estado', 'BUENO');

        // Filtro por organización
        if ($request->filled('organizacion_id')) {
            $query->whereHas('apiario.productor', function ($q) use ($request) {
                $q->where('organizacion_id', $request->organizacion_id);
            });
        }

        // Filtro por productor
        if ($request->filled('productor_id')) {
            $query->whereHas('apiario', function ($q) use ($request) {
                $q->where('productor_id', $request->productor_id);
            });
        }

        // Filtro por producto
        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_cosecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_cosecha', '<=', $request->fecha_hasta);
        }

        // Filtro por cantidad mínima
        if ($request->filled('cantidad_min')) {
            $query->where('cantidad_kg', '>=', $request->cantidad_min);
        }

        // Filtro por cantidad máxima
        if ($request->filled('cantidad_max')) {
            $query->where('cantidad_kg', '<=', $request->cantidad_max);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha_cosecha');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Validar campos permitidos para ordenamiento
        $allowedSortFields = ['fecha_cosecha', 'cantidad_kg', 'precio_compra', 'nro_acta', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'fecha_cosecha';
        }
        
        $query->orderBy($sortBy, $sortDir);

        // Paginación
        $perPage = $request->get('per_page', 50);
        $acopios = $query->paginate($perPage);

        // Calcular totales
        $totales = [
            'total_acopios' => $acopios->total(),
            'total_kg' => $acopios->sum('cantidad_kg'),
            'total_costo' => $acopios->sum(function ($a) {
                return $a->cantidad_kg * $a->precio_compra;
            }),
        ];

        // Agregar días en espera a cada acopio
        $acopios->getCollection()->transform(function ($acopio) {
            $acopio->dias_espera = now()->diffInDays($acopio->fecha_cosecha);
            $acopio->costo_total = $acopio->cantidad_kg * $acopio->precio_compra;
            $acopio->productor_nombre = $acopio->apiario->productor->nombre . ' ' . $acopio->apiario->productor->apellidos;
            $acopio->organizacion_nombre = $acopio->apiario->productor->organizacion->nombre_organiza ?? 'Independiente';
            return $acopio;
        });

        return response()->json([
            'acopios' => $acopios,
            'totales' => $totales,
            'filtros_aplicados' => $request->only([
                'organizacion_id',
                'productor_id',
                'producto_id',
                'fecha_desde',
                'fecha_hasta',
                'cantidad_min',
                'cantidad_max'
            ]),
        ]);
    }

    /**
     * Procesar acopios masivamente
     * POST /procesamiento-masivo/procesar
     */
    public function procesarMasivo(Request $request)
    {
        try {
            $request->validate([
                'acopio_ids' => 'required|array|min:1',
                'acopio_ids.*' => 'exists:acopio_cosechas,id',
                'tipo_procesamiento' => 'required|in:AUTOMATICO,MANUAL,SELECCION',
                'observaciones' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        $inicio = microtime(true);
        
        DB::beginTransaction();
        try {
            $acopioIds = $request->acopio_ids;
            $procesados = 0;
            $fallidos = 0;
            $totalKg = 0;
            $totalCosto = 0;
            $errores = [];

            // Verificar y procesar cada acopio
            foreach ($acopioIds as $acopioId) {
                $acopio = AcopioCosecha::find($acopioId);

                if (!$acopio) {
                    $fallidos++;
                    $errores[] = "Acopio ID {$acopioId} no encontrado";
                    continue;
                }

                if ($acopio->estado !== 'BUENO') {
                    $fallidos++;
                    $errores[] = "Acopio ID {$acopioId} no está en estado BUENO (actual: {$acopio->estado})";
                    continue;
                }

                // Cambiar estado a EN_PROCESO (sin auditar para evitar conflictos)
                $acopio->disableAuditing();
                $acopio->update(['estado' => 'EN_PROCESO']);
                $acopio->enableAuditing();
                
                $procesados++;
                $totalKg += $acopio->cantidad_kg;
                $totalCosto += ($acopio->cantidad_kg * $acopio->precio_compra);
            }

            $duracion = round(microtime(true) - $inicio, 2);

            // Crear log del procesamiento
            $log = ProcesamientoMasivoLog::create([
                'usuario_id' => Auth::id(),
                'tipo_procesamiento' => $request->tipo_procesamiento,
                'acopios_procesados' => $procesados,
                'acopios_rechazados' => 0,
                'acopios_fallidos' => $fallidos,
                'total_kg_procesado' => $totalKg,
                'total_costo' => $totalCosto,
                'filtros_aplicados' => $request->get('filtros_aplicados', []),
                'acopio_ids' => $acopioIds,
                'observaciones' => $request->observaciones,
                'fecha_ejecucion' => now(),
                'duracion_segundos' => max(1, (int) $duracion), // Convertir a entero, mínimo 1 segundo
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procesamiento masivo completado',
                'log_id' => $log->id,
                'resumen' => [
                    'acopios_procesados' => $procesados,
                    'acopios_fallidos' => $fallidos,
                    'total_kg' => number_format($totalKg, 2),
                    'total_costo' => number_format($totalCosto, 2),
                    'duracion_segundos' => $duracion,
                ],
                'errores' => $errores,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error en procesamiento masivo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error en el procesamiento masivo',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Rechazar acopios masivamente
     * POST /procesamiento-masivo/rechazar
     */
    public function rechazarAcopios(Request $request)
    {
        try {
            $request->validate([
                'rechazos' => 'required|array|min:1',
                'rechazos.*.acopio_id' => 'required|exists:acopio_cosechas,id',
                'rechazos.*.motivo' => 'required|in:CALIDAD_INSUFICIENTE,HUMEDAD_ALTA,CONTAMINACION,DOCUMENTACION_INCOMPLETA,TEMPERATURA_INCORRECTA,PESO_INCORRECTO,ENVASE_INADECUADO,OTRO',
                'rechazos.*.observaciones' => 'nullable|string|max:500',
                'rechazos.*.accion_correctiva' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $rechazados = 0;
            $fallidos = 0;
            $errores = [];

            foreach ($request->rechazos as $rechazoData) {
                $acopio = AcopioCosecha::find($rechazoData['acopio_id']);

                if (!$acopio) {
                    $fallidos++;
                    $errores[] = "Acopio ID {$rechazoData['acopio_id']} no encontrado";
                    continue;
                }

                if ($acopio->estado !== 'BUENO') {
                    $fallidos++;
                    $errores[] = "Acopio ID {$rechazoData['acopio_id']} no puede ser rechazado (estado: {$acopio->estado})";
                    continue;
                }

                // Cambiar estado del acopio a RECHAZADO (sin auditar para evitar conflictos)
                $acopio->disableAuditing();
                $acopio->update(['estado' => 'RECHAZADO']);
                $acopio->enableAuditing();

                // Crear registro de rechazo
                AcopioRechazo::create([
                    'acopio_cosecha_id' => $acopio->id,
                    'motivo_rechazo' => $rechazoData['motivo'],
                    'observaciones' => $rechazoData['observaciones'] ?? null,
                    'accion_correctiva' => $rechazoData['accion_correctiva'] ?? null,
                    'rechazado_por' => Auth::id(),
                    'estado_devolucion' => 'PENDIENTE',
                    'fecha_rechazo' => now(),
                ]);

                $rechazados++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rechazos procesados correctamente',
                'resumen' => [
                    'acopios_rechazados' => $rechazados,
                    'acopios_fallidos' => $fallidos,
                ],
                'errores' => $errores,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al procesar rechazos: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar rechazos',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de procesamiento
     * GET /procesamiento-masivo/estadisticas
     */
    public function obtenerEstadisticas(Request $request)
    {
        // Acopios disponibles por producto
        $acopiosPorProducto = AcopioCosecha::select('producto_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(cantidad_kg) as total_kg'))
            ->where('estado', 'BUENO')
            ->with('producto:id,nombre_producto')
            ->groupBy('producto_id')
            ->get()
            ->map(function ($item) {
                return [
                    'producto' => $item->producto->nombre_producto ?? 'N/A',
                    'total_acopios' => $item->total,
                    'total_kg' => number_format($item->total_kg, 2),
                ];
            });

        // Acopios disponibles por organización
        $acopiosPorOrganizacion = DB::table('acopio_cosechas as ac')
            ->join('apiarios as a', 'ac.apiario_id', '=', 'a.id')
            ->join('productores as p', 'a.productor_id', '=', 'p.id')
            ->leftJoin('organizaciones as o', 'p.organizacion_id', '=', 'o.id')
            ->select(
                DB::raw('COALESCE(o.nombre_organizacion, \'Independiente\') as organizacion'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(ac.cantidad_kg) as total_kg')
            )
            ->where('ac.estado', 'BUENO')
            ->whereNull('ac.deleted_at')
            ->groupBy('o.nombre_organizacion')
            ->get();

        // Acopios más antiguos pendientes
        $acopiosAntiguos = AcopioCosecha::where('estado', 'BUENO')
            ->with(['apiario.productor', 'producto'])
            ->orderBy('fecha_cosecha', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($acopio) {
                return [
                    'id' => $acopio->id,
                    'fecha_cosecha' => $acopio->fecha_cosecha,
                    'dias_espera' => now()->diffInDays($acopio->fecha_cosecha),
                    'productor' => $acopio->apiario->productor->nombre . ' ' . $acopio->apiario->productor->apellidos,
                    'producto' => $acopio->producto->nombre_producto ?? 'N/A',
                    'cantidad_kg' => $acopio->cantidad_kg,
                ];
            });

        // Tasa de rechazo (últimos 30 días)
        $totalProcesados = ProcesamientoMasivoLog::where('created_at', '>=', now()->subDays(30))
            ->sum('acopios_procesados');
        $totalRechazados = AcopioRechazo::where('created_at', '>=', now()->subDays(30))->count();
        $tasaRechazo = $totalProcesados > 0 ? round(($totalRechazados / ($totalProcesados + $totalRechazados)) * 100, 2) : 0;

        return response()->json([
            'acopios_por_producto' => $acopiosPorProducto,
            'acopios_por_organizacion' => $acopiosPorOrganizacion,
            'acopios_antiguos' => $acopiosAntiguos,
            'tasa_rechazo' => $tasaRechazo,
            'total_disponibles' => AcopioCosecha::where('estado', 'BUENO')->count(),
            'total_kg_disponibles' => AcopioCosecha::where('estado', 'BUENO')->sum('cantidad_kg'),
        ]);
    }

    /**
     * Obtener historial de procesamientos
     * GET /procesamiento-masivo/historial
     */
    public function historial(Request $request)
    {
        $query = ProcesamientoMasivoLog::with('usuario:id,username')
            ->orderBy('created_at', 'desc');

        // Filtros opcionales
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('tipo_procesamiento')) {
            $query->where('tipo_procesamiento', $request->tipo_procesamiento);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $perPage = $request->get('per_page', 20);
        $historial = $query->paginate($perPage);

        return response()->json($historial);
    }
}
