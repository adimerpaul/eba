<?php

namespace App\Http\Controllers;

use App\Models\ControlProceso;
use App\Models\AcopioCosecha;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlProcesoController extends Controller
{
    public function index(Request $request)
    {
        $query = ControlProceso::with([
            'acopios.apiario.productor',
            'tanque.planta',
            'producto',
            'user'
        ]);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tanque_id')) {
            $query->where('tanque_id', $request->tanque_id);
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha_proceso', [$request->fecha_inicio, $request->fecha_fin]);
        }

        return $query->orderByDesc('id')->paginate($request->per_page ?? 20);
    }

    public function show($id)
    {
        return ControlProceso::with([
            'acopios.apiario.productor',
            'acopios.producto',
            'tanque.planta',
            'producto',
            'user',
            'lotes.producto'
        ])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'acopio_ids' => 'required|array|min:1',
            'acopio_ids.*' => 'exists:acopio_cosechas,id',
            'tanque_id' => 'required|exists:tanques,id',
            'producto_id' => 'required|exists:productos,id',
            'temperatura_proceso' => 'nullable|numeric',
            'tiempo_proceso_horas' => 'nullable|numeric',
            'metodo_proceso' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($request) {
            $acopios = AcopioCosecha::whereIn('id', $request->acopio_ids)
                ->where('estado', 'BUENO')
                ->get();

            if ($acopios->count() !== count($request->acopio_ids)) {
                return response()->json([
                    'message' => 'Algunos acopios no estan disponibles o no existen'
                ], 422);
            }

            $cantidad_total = $acopios->sum('cantidad_kg');

            // Validar capacidad del tanque
            $tanque = \App\Models\Tanque::findOrFail($request->tanque_id);
            
            // Calcular ocupación actual del tanque
            $ocupacion_procesos = \App\Models\ControlProceso::where('tanque_id', $tanque->id)
                ->where('estado', 'EN_PROCESO')
                ->sum('cantidad_entrada_kg');
            
            $ocupacion_lotes = \App\Models\Lote::where('tanque_id', $tanque->id)
                ->sum('cantidad_kg');
            
            $ocupacion_actual = $ocupacion_procesos + $ocupacion_lotes;
            $capacidad_disponible = $tanque->capacidad_kg - $ocupacion_actual;

            if ($cantidad_total > $capacidad_disponible) {
                return response()->json([
                    'message' => "Capacidad insuficiente. Disponible: {$capacidad_disponible} kg, Requerido: {$cantidad_total} kg",
                    'capacidad_disponible' => $capacidad_disponible,
                    'cantidad_requerida' => $cantidad_total,
                    'ocupacion_actual' => $ocupacion_actual,
                    'capacidad_total' => $tanque->capacidad_kg
                ], 422);
            }

            $control = ControlProceso::create([
                'tanque_id' => $request->tanque_id,
                'producto_id' => $request->producto_id,
                'fecha_proceso' => now(),
                'cantidad_entrada_kg' => $cantidad_total,
                'temperatura_proceso' => $request->temperatura_proceso,
                'tiempo_proceso_horas' => $request->tiempo_proceso_horas,
                'metodo_proceso' => $request->metodo_proceso,
                'estado' => 'EN_PROCESO',
                'user_id' => auth()->id(),
                'observaciones' => $request->observaciones
            ]);

            foreach ($acopios as $acopio) {
                $control->acopios()->attach($acopio->id, [
                    'cantidad_kg' => $acopio->cantidad_kg
                ]);
                
                $acopio->update(['estado' => 'EN_PROCESO']);
            }

            return $control->load(['acopios', 'tanque', 'producto']);
        });
    }

    public function finalizarProceso(Request $request, $id)
    {
        $request->validate([
            'cantidad_salida_kg' => 'required|numeric|min:0',
            'temperatura_final' => 'nullable|numeric',
            'humedad_final' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $control = ControlProceso::findOrFail($id);

            if ($control->estado === 'FINALIZADO') {
                return response()->json([
                    'message' => 'El proceso ya esta finalizado'
                ], 422);
            }

            $entrada = $control->cantidad_entrada_kg;
            $salida = $request->cantidad_salida_kg;
            
            if ($salida > $entrada) {
                return response()->json([
                    'message' => 'La cantidad de salida no puede ser mayor a la entrada'
                ], 422);
            }

            $merma = $entrada - $salida;
            $merma_pct = $entrada > 0 ? ($merma / $entrada) * 100 : 0;

            $control->update([
                'cantidad_salida_kg' => $salida,
                'merma_kg' => $merma,
                'merma_porcentaje' => $merma_pct,
                'dato4' => $request->humedad_final,
                'estado' => 'FINALIZADO'
            ]);

            foreach ($control->acopios as $acopio) {
                $acopio->update(['estado' => 'PROCESADO']);
            }

            return $control->load(['acopios', 'tanque', 'producto', 'lotes']);
        });
    }

    public function update(Request $request, $id)
    {
        $control = ControlProceso::findOrFail($id);

        if ($control->estado === 'FINALIZADO') {
            return response()->json([
                'message' => 'No se puede modificar un proceso finalizado'
            ], 422);
        }

        $control->update($request->only([
            'temperatura_proceso',
            'tiempo_proceso_horas',
            'metodo_proceso',
            'observaciones'
        ]));

        return $control->load(['acopios', 'tanque', 'producto']);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $control = ControlProceso::findOrFail($id);

            if ($control->lotes()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar un proceso que tiene lotes asociados'
                ], 422);
            }

            foreach ($control->acopios as $acopio) {
                $acopio->update(['estado' => 'BUENO']);
            }

            $control->delete();

            return response()->json(['deleted' => true]);
        });
    }

    public function finalizados(Request $request)
    {
        $query = ControlProceso::where('estado', 'FINALIZADO')
            ->with(['tanque', 'producto', 'acopios']);

        if ($request->has('tanque_id')) {
            $query->where('tanque_id', $request->tanque_id);
        }

        return $query->orderByDesc('id')->get()->map(function ($proceso) {
            $asignado = $proceso->lotes()->sum('cantidad_kg');
            $proceso->disponible_kg = $proceso->cantidad_salida_kg - $asignado;
            return $proceso;
        });
    }
}
