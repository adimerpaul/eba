<?php

namespace App\Http\Controllers;

use App\Models\AcopioTransporteLog;
use App\Models\AcopioCosecha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para gestión de registros de transporte de acopios.
 * Permite trazabilidad completa del transporte de materia prima desde apiarios.
 */
class AcopioTransporteLogController extends Controller
{
    /**
     * Listar registros de transporte de un acopio específico
     * GET /api/acopio-cosechas/{acopioId}/transporte-logs
     */
    public function index(Request $request, $acopioId)
    {
        $query = AcopioTransporteLog::with([
            'transporte',
            'registradoPor:id,name'
        ])->where('acopio_cosecha_id', $acopioId);

        // Filtro por alertas
        if ($request->filled('con_alertas')) {
            $query->conAlertas();
        }

        // Ordenar por fecha más reciente
        $query->orderByDesc('fecha_hora_salida');

        $logs = $query->get();

        // Agregar atributos calculados
        $logs->each(function ($log) {
            $log->variacion_temperatura = $log->variacion_temperatura;
            $log->estado_cumplimiento = $log->estado_cumplimiento;
        });

        return response()->json($logs);
    }

    /**
     * Obtener detalle de un registro de transporte
     * GET /api/transporte-logs/{id}
     */
    public function show($id)
    {
        $log = AcopioTransporteLog::with([
            'acopioCosecha.apiario.productor',
            'transporte',
            'registradoPor'
        ])->findOrFail($id);

        $log->variacion_temperatura = $log->variacion_temperatura;
        $log->estado_cumplimiento = $log->estado_cumplimiento;

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * Registrar nuevo transporte de acopio
     * POST /api/acopio-cosechas/{acopioId}/transporte-logs
     */
    public function store(Request $request, $acopioId)
    {
        $validated = $request->validate([
            'transporte_id' => 'required|exists:transportes,id',
            'lugar_origen' => 'required|string|max:200',
            'lugar_destino' => 'nullable|string|max:200',
            'distancia_km' => 'nullable|numeric|min:0|max:9999.99',
            'temperatura_salida' => 'nullable|numeric|min:-10|max:60',
            'temperatura_llegada' => 'nullable|numeric|min:-10|max:60',
            'temperatura_maxima' => 'nullable|numeric|min:-10|max:60',
            'temperatura_minima' => 'nullable|numeric|min:-10|max:60',
            'fecha_hora_salida' => 'nullable|date',
            'fecha_hora_llegada' => 'nullable|date|after:fecha_hora_salida',
            'tiempo_transporte_horas' => 'nullable|numeric|min:0|max:99.99',
            'condiciones_envase' => 'nullable|string|max:50',
            'condiciones_vehiculo' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // Verificar que el acopio existe
        $acopio = AcopioCosecha::findOrFail($acopioId);

        return DB::transaction(function () use ($validated, $acopioId) {
            $log = AcopioTransporteLog::create([
                ...$validated,
                'acopio_cosecha_id' => $acopioId,
                'registrado_por' => Auth::id(),
            ]);

            $log->load(['transporte', 'registradoPor']);
            $log->variacion_temperatura = $log->variacion_temperatura;
            $log->estado_cumplimiento = $log->estado_cumplimiento;

            return response()->json([
                'success' => true,
                'message' => 'Registro de transporte creado exitosamente',
                'data' => $log
            ], 201);
        });
    }

    /**
     * Actualizar registro de transporte
     * PUT /api/transporte-logs/{id}
     */
    public function update(Request $request, $id)
    {
        $log = AcopioTransporteLog::findOrFail($id);

        $validated = $request->validate([
            'transporte_id' => 'sometimes|required|exists:transportes,id',
            'lugar_origen' => 'sometimes|required|string|max:200',
            'lugar_destino' => 'nullable|string|max:200',
            'distancia_km' => 'nullable|numeric|min:0|max:9999.99',
            'temperatura_salida' => 'nullable|numeric|min:-10|max:60',
            'temperatura_llegada' => 'nullable|numeric|min:-10|max:60',
            'temperatura_maxima' => 'nullable|numeric|min:-10|max:60',
            'temperatura_minima' => 'nullable|numeric|min:-10|max:60',
            'fecha_hora_salida' => 'nullable|date',
            'fecha_hora_llegada' => 'nullable|date|after:fecha_hora_salida',
            'tiempo_transporte_horas' => 'nullable|numeric|min:0|max:99.99',
            'condiciones_envase' => 'nullable|string|max:50',
            'condiciones_vehiculo' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($log, $validated) {
            $log->update($validated);

            $log->load(['transporte', 'registradoPor']);
            $log->variacion_temperatura = $log->variacion_temperatura;
            $log->estado_cumplimiento = $log->estado_cumplimiento;

            return response()->json([
                'success' => true,
                'message' => 'Registro de transporte actualizado exitosamente',
                'data' => $log
            ]);
        });
    }

    /**
     * Eliminar registro de transporte
     * DELETE /api/transporte-logs/{id}
     */
    public function destroy($id)
    {
        $log = AcopioTransporteLog::findOrFail($id);

        return DB::transaction(function () use ($log) {
            $log->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro de transporte eliminado exitosamente'
            ]);
        });
    }

    /**
     * Obtener estadísticas de transporte
     * GET /api/transporte-logs/estadisticas
     */
    public function estadisticas(Request $request)
    {
        $query = AcopioTransporteLog::query();

        // Filtro por rango de fechas
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->entreFechas($request->fecha_desde, $request->fecha_hasta);
        }

        // Filtro por transporte
        if ($request->filled('transporte_id')) {
            $query->deTransporte($request->transporte_id);
        }

        $estadisticas = [
            'total_transportes' => $query->count(),
            'con_alerta_temperatura' => (clone $query)->where('alerta_temperatura', true)->count(),
            'con_alerta_tiempo' => (clone $query)->where('alerta_tiempo', true)->count(),
            'promedio_tiempo_horas' => round((clone $query)->avg('tiempo_transporte_horas') ?? 0, 2),
            'promedio_temperatura_llegada' => round((clone $query)->avg('temperatura_llegada') ?? 0, 2),
            'temperatura_maxima_registrada' => round((clone $query)->max('temperatura_maxima') ?? 0, 2),
            'distancia_total_km' => round((clone $query)->sum('distancia_km') ?? 0, 2),
        ];

        // Distribución por condiciones
        $distribucionCondiciones = [
            'envase' => (clone $query)->select('condiciones_envase', DB::raw('count(*) as total'))
                ->groupBy('condiciones_envase')
                ->get()
                ->pluck('total', 'condiciones_envase'),
            'vehiculo' => (clone $query)->select('condiciones_vehiculo', DB::raw('count(*) as total'))
                ->groupBy('condiciones_vehiculo')
                ->get()
                ->pluck('total', 'condiciones_vehiculo'),
        ];

        return response()->json([
            'success' => true,
            'estadisticas' => $estadisticas,
            'distribucion' => $distribucionCondiciones
        ]);
    }

    /**
     * Obtener registros con alertas
     * GET /api/transporte-logs/alertas
     */
    public function alertas(Request $request)
    {
        $query = AcopioTransporteLog::with([
            'acopioCosecha.apiario.productor',
            'transporte'
        ])->conAlertas();

        // Filtro por tipo de alerta
        if ($request->filled('tipo_alerta')) {
            if ($request->tipo_alerta === 'temperatura') {
                $query->where('alerta_temperatura', true);
            } elseif ($request->tipo_alerta === 'tiempo') {
                $query->where('alerta_tiempo', true);
            }
        }

        $logs = $query->orderByDesc('fecha_hora_salida')->paginate($request->per_page ?? 20);

        return response()->json($logs);
    }
}
