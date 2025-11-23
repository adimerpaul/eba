<?php

namespace App\Http\Controllers;

use App\Models\AcopioRechazo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcopioRechazoController extends Controller
{
    /**
     * Obtener listado de acopios rechazados con filtros y paginación
     */
    public function index(Request $request)
    {
        $query = AcopioRechazo::with([
            'acopioCosecha.producto',
            'acopioCosecha.apiario.productor.organizacion',
            'rechazadoPor',
            'devueltoPor'
        ]);

        // Filtros
        if ($request->filled('estado_devolucion')) {
            $query->where('estado_devolucion', $request->estado_devolucion);
        }

        if ($request->filled('motivo_rechazo')) {
            $query->where('motivo_rechazo', $request->motivo_rechazo);
        }

        if ($request->filled('organizacion_id')) {
            $query->whereHas('acopioCosecha.apiario', function ($q) use ($request) {
                $q->where('organizacion_id', $request->organizacion_id);
            });
        }

        if ($request->filled('productor_id')) {
            $query->whereHas('acopioCosecha.apiario', function ($q) use ($request) {
                $q->where('productor_id', $request->productor_id);
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_rechazo', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_rechazo', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha_rechazo');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $rechazos = $query->paginate($perPage);

        // Agregar días desde rechazo
        $rechazos->getCollection()->transform(function ($rechazo) {
            $rechazo->dias_desde_rechazo = now()->diffInDays($rechazo->fecha_rechazo);
            return $rechazo;
        });

        return response()->json($rechazos);
    }

    /**
     * Obtener estadísticas de rechazos
     */
    public function estadisticas()
    {
        $stats = [
            'pendientes' => AcopioRechazo::where('estado_devolucion', 'PENDIENTE')->count(),
            'notificados' => AcopioRechazo::where('estado_devolucion', 'NOTIFICADO')->count(),
            'devueltos' => AcopioRechazo::where('estado_devolucion', 'DEVUELTO')->count(),
            'cancelados' => AcopioRechazo::where('estado_devolucion', 'CANCELADO')->count(),
            
            'total_rechazados' => AcopioRechazo::count(),
            
            // Rechazos por motivo
            'por_motivo' => AcopioRechazo::select('motivo_rechazo', DB::raw('count(*) as total'))
                ->groupBy('motivo_rechazo')
                ->get(),
            
            // Rechazos últimos 30 días
            'ultimos_30_dias' => AcopioRechazo::where('fecha_rechazo', '>=', now()->subDays(30))
                ->count(),
            
            // Tasa de devolución (devueltos / total)
            'tasa_devolucion' => AcopioRechazo::count() > 0 
                ? round((AcopioRechazo::where('estado_devolucion', 'DEVUELTO')->count() / AcopioRechazo::count()) * 100, 2)
                : 0,
            
            // Tiempo promedio de devolución (en días)
            'tiempo_promedio_devolucion' => AcopioRechazo::whereNotNull('fecha_devolucion')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (fecha_devolucion - fecha_rechazo)) / 86400) as promedio')
                ->value('promedio')
        ];

        return response()->json($stats);
    }

    /**
     * Marcar rechazo como notificado
     */
    public function marcarComoNotificado($id)
    {
        try {
            $rechazo = AcopioRechazo::findOrFail($id);
            
            if ($rechazo->estado_devolucion !== 'PENDIENTE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden notificar rechazos en estado PENDIENTE'
                ], 400);
            }

            $rechazo->marcarComoNotificado();

            return response()->json([
                'success' => true,
                'message' => 'Rechazo marcado como notificado correctamente',
                'data' => $rechazo->load([
                    'acopioCosecha.producto',
                    'acopioCosecha.productor',
                    'rechazadoPorUsuario'
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como notificado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar rechazo como devuelto
     */
    public function marcarComoDevuelto($id)
    {
        try {
            $rechazo = AcopioRechazo::findOrFail($id);
            
            if (!in_array($rechazo->estado_devolucion, ['PENDIENTE', 'NOTIFICADO'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden marcar como devueltos rechazos PENDIENTES o NOTIFICADOS'
                ], 400);
            }

            $rechazo->marcarComoDevuelto();

            return response()->json([
                'success' => true,
                'message' => 'Rechazo marcado como devuelto correctamente',
                'data' => $rechazo->load([
                    'acopioCosecha.producto',
                    'acopioCosecha.productor',
                    'rechazadoPorUsuario',
                    'devueltoPorUsuario'
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como devuelto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle de un rechazo específico
     */
    public function show($id)
    {
        try {
            $rechazo = AcopioRechazo::with([
                'acopioCosecha.producto',
                'acopioCosecha.apiario.productor.organizacion',
                'rechazadoPor',
                'devueltoPor',
                'procesamientoLog'
            ])->findOrFail($id);

            $rechazo->dias_desde_rechazo = now()->diffInDays($rechazo->fecha_rechazo);

            return response()->json([
                'success' => true,
                'data' => $rechazo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle del rechazo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener rechazos pendientes de un productor específico
     */
    public function rechazosPorProductor($productorId)
    {
        $rechazos = AcopioRechazo::with([
            'acopioCosecha.producto',
            'acopioCosecha.apiario.productor.organizacion',
            'rechazadoPor'
        ])
        ->whereHas('acopioCosecha.apiario', function ($q) use ($productorId) {
            $q->where('productor_id', $productorId);
        })
        ->where('estado_devolucion', '!=', 'DEVUELTO')
        ->orderBy('fecha_rechazo', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $rechazos
        ]);
    }

    /**
     * Cancelar un rechazo (en caso de error)
     */
    public function cancelar($id, Request $request)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $rechazo = AcopioRechazo::findOrFail($id);
            
            // Cambiar estado del rechazo
            $rechazo->estado_devolucion = 'CANCELADO';
            $rechazo->observaciones .= "\n\nCANCELADO: " . $request->motivo_cancelacion;
            $rechazo->save();

            // Restaurar el acopio a BUENO
            $rechazo->acopioCosecha->estado = 'BUENO';
            $rechazo->acopioCosecha->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rechazo cancelado correctamente. El acopio ha sido restaurado a estado BUENO.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar rechazo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
