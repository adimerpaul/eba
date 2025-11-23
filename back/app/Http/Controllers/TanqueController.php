<?php

namespace App\Http\Controllers;

use App\Models\Tanque;
use App\Models\ControlProceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TanqueController extends Controller
{
    /**
     * Listar todos los tanques con información de capacidad y ocupación
     * GET /api/tanques
     */
    public function index(Request $request)
    {
        $query = Tanque::with('planta:id,nombre_planta')
            ->where('id', '>', 0);

        // Filtro por estado operativo
        if ($request->filled('estado_operativo')) {
            $query->where('estado_operativo', $request->estado_operativo);
        }

        // Filtro por planta
        if ($request->filled('planta_id')) {
            $query->where('planta_id', $request->planta_id);
        }

        $tanques = $query->get();

        // Calcular ocupación actual de cada tanque
        $tanques->each(function ($tanque) {
            // Cantidad actual en procesos EN_PROCESO
            $enProceso = ControlProceso::where('tanque_id', $tanque->id)
                ->where('estado', 'EN_PROCESO')
                ->sum('cantidad_entrada_kg');

            // Cantidad en lotes almacenados
            $enLotes = DB::table('lotes')
                ->where('tanque_id', $tanque->id)
                ->whereNull('deleted_at')
                ->sum('cantidad_kg');

            $tanque->ocupacion_actual_kg = round($enProceso + $enLotes, 2);
            $tanque->capacidad_disponible_kg = $tanque->capacidad_kg 
                ? round($tanque->capacidad_kg - $tanque->ocupacion_actual_kg, 2) 
                : null;
            $tanque->porcentaje_ocupacion = $tanque->capacidad_kg && $tanque->capacidad_kg > 0
                ? round(($tanque->ocupacion_actual_kg / $tanque->capacidad_kg) * 100, 2)
                : null;
        });

        return response()->json($tanques);
    }

    /**
     * Obtener ocupación detallada de un tanque
     * GET /api/tanques/{id}/ocupacion
     */
    public function ocupacion($id)
    {
        $tanque = Tanque::with('planta')->findOrFail($id);

        // Procesos activos en el tanque
        $procesosActivos = ControlProceso::with(['acopios', 'producto'])
            ->where('tanque_id', $id)
            ->where('estado', 'EN_PROCESO')
            ->get();

        // Lotes almacenados en el tanque
        $lotesAlmacenados = DB::table('lotes')
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('lotes.tanque_id', $id)
            ->whereNull('lotes.deleted_at')
            ->select([
                'lotes.id',
                'lotes.codigo_lote',
                'lotes.cantidad_kg',
                'lotes.fecha_envasado',
                'productos.nombre_producto'
            ])
            ->get();

        $ocupacionTotal = $procesosActivos->sum('cantidad_entrada_kg') + $lotesAlmacenados->sum('cantidad_kg');

        return response()->json([
            'success' => true,
            'tanque' => $tanque,
            'ocupacion' => [
                'total_kg' => round($ocupacionTotal, 2),
                'disponible_kg' => $tanque->capacidad_kg 
                    ? round($tanque->capacidad_kg - $ocupacionTotal, 2) 
                    : null,
                'porcentaje' => $tanque->capacidad_kg && $tanque->capacidad_kg > 0
                    ? round(($ocupacionTotal / $tanque->capacidad_kg) * 100, 2)
                    : null,
            ],
            'procesos_activos' => $procesosActivos,
            'lotes_almacenados' => $lotesAlmacenados,
        ]);
    }
}
