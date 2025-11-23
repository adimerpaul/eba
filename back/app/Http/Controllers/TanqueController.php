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
            $enLotes = DB::table('traza.lotes')
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
        $lotesAlmacenados = DB::table('traza.lotes')
            ->join('traza.productos', 'lotes.producto_id', '=', 'productos.id')
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

    /**
     * Mostrar un tanque específico
     * GET /api/tanques/{id}
     */
    public function show($id)
    {
        $tanque = Tanque::with('planta')->findOrFail($id);
        
        $enProceso = ControlProceso::where('tanque_id', $tanque->id)
            ->where('estado', 'EN_PROCESO')
            ->sum('cantidad_entrada_kg');

        $enLotes = DB::table('lotes')
            ->where('tanque_id', $tanque->id)
            ->whereNull('deleted_at')
            ->sum('cantidad_kg');

        $tanque->ocupacion_actual_kg = round($enProceso + $enLotes, 2);
        $tanque->capacidad_disponible_kg = $tanque->capacidad_kg 
            ? round($tanque->capacidad_kg - $tanque->ocupacion_actual_kg, 2) 
            : null;

        return response()->json($tanque);
    }

    /**
     * Crear un nuevo tanque
     * POST /api/tanques
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_tanque' => 'required|string|max:5|unique:tanques,codigo_tanque',
            'nombre_tanque' => 'required|string|max:150',
            'planta_id' => 'nullable|exists:plantas,id',
            'capacidad_litros' => 'nullable|numeric|min:0',
            'capacidad_kg' => 'nullable|numeric|min:0',
            'estado_operativo' => 'nullable|in:OPERATIVO,MANTENIMIENTO,FUERA_SERVICIO',
            'descripcion' => 'nullable|string'
        ]);

        $tanque = Tanque::create($validated);
        return response()->json($tanque->load('planta'), 201);
    }

    /**
     * Actualizar un tanque existente
     * PUT /api/tanques/{id}
     */
    public function update(Request $request, $id)
    {
        $tanque = Tanque::findOrFail($id);

        $validated = $request->validate([
            'codigo_tanque' => 'required|string|max:5|unique:tanques,codigo_tanque,' . $id,
            'nombre_tanque' => 'required|string|max:150',
            'planta_id' => 'nullable|exists:plantas,id',
            'capacidad_litros' => 'nullable|numeric|min:0',
            'capacidad_kg' => 'nullable|numeric|min:0',
            'estado_operativo' => 'nullable|in:OPERATIVO,MANTENIMIENTO,FUERA_SERVICIO',
            'descripcion' => 'nullable|string'
        ]);

        $tanque->update($validated);
        return response()->json($tanque->load('planta'));
    }

    /**
     * Eliminar un tanque
     * DELETE /api/tanques/{id}
     */
    public function destroy($id)
    {
        $tanque = Tanque::findOrFail($id);

        $procesosActivos = ControlProceso::where('tanque_id', $id)
            ->where('estado', 'EN_PROCESO')
            ->count();

        if ($procesosActivos > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el tanque porque tiene procesos activos'
            ], 422);
        }

        $lotesAlmacenados = DB::table('traza.lotes')
            ->where('tanque_id', $id)
            ->whereNull('deleted_at')
            ->count();

        if ($lotesAlmacenados > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el tanque porque tiene lotes almacenados'
            ], 422);
        }

        $tanque->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Obtener historial de procesos de un tanque
     * GET /api/tanques/{id}/historial
     */
    public function historial($id)
    {
        $procesos = ControlProceso::with(['producto', 'acopios'])
            ->where('tanque_id', $id)
            ->orderByDesc('fecha_inicio')
            ->limit(10)
            ->get();

        return response()->json($procesos);
    }

    /**
     * Obtener estadísticas generales de tanques
     * GET /api/tanques/estadisticas
     */
    public function estadisticas()
    {
        $totalTanques = Tanque::count();
        $operativos = Tanque::where('estado_operativo', 'OPERATIVO')->count();
        
        $tanques = Tanque::whereNotNull('capacidad_kg')->get();
        
        $ocupacionTotal = 0;
        $capacidadTotal = 0;
        $tanquesLlenos = 0;

        foreach ($tanques as $tanque) {
            $enProceso = ControlProceso::where('tanque_id', $tanque->id)
                ->where('estado', 'EN_PROCESO')
                ->sum('cantidad_entrada_kg');

            $enLotes = DB::table('traza.lotes')
                ->where('tanque_id', $tanque->id)
                ->whereNull('deleted_at')
                ->sum('cantidad_kg');

            $ocupacion = $enProceso + $enLotes;
            $ocupacionTotal += $ocupacion;
            $capacidadTotal += $tanque->capacidad_kg;

            $porcentaje = ($ocupacion / $tanque->capacidad_kg) * 100;
            if ($porcentaje > 90) {
                $tanquesLlenos++;
            }
        }

        return response()->json([
            'total_tanques' => $totalTanques,
            'operativos' => $operativos,
            'en_mantenimiento' => Tanque::where('estado_operativo', 'MANTENIMIENTO')->count(),
            'fuera_servicio' => Tanque::where('estado_operativo', 'FUERA_SERVICIO')->count(),
            'capacidad_total_kg' => round($capacidadTotal, 2),
            'ocupacion_total_kg' => round($ocupacionTotal, 2),
            'ocupacion_promedio' => $capacidadTotal > 0 ? round(($ocupacionTotal / $capacidadTotal) * 100, 2) : 0,
            'tanques_llenos' => $tanquesLlenos
        ]);
    }
}
