<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    public function index(AcopioCosecha $cosecha)
    {
        $lotes = $cosecha->lotes()
            ->with(['producto','tanque'])
            ->orderByDesc('id')
            ->get();

        $total     = (float) $lotes->sum('cantidad_kg');
        $capacidad = (float) ($cosecha->cantidad_kg ?? 0);

        return response()->json([
            'rows' => $lotes,
            'meta' => [
                'asignado_kg'  => $total,
                'capacidad_kg' => $capacidad,
                'restante_kg'  => max(0, $capacidad - $total),
            ],
        ]);
    }

    public function store(Request $request, AcopioCosecha $cosecha)
    {
        return DB::transaction(function () use ($request, $cosecha) {
            $producto = Producto::findOrFail($request->producto_id);
            if ((int) $producto->tipo_id !== 3) {
                return response()->json(['message' => 'El producto debe ser de tipo 3.'], 422);
            }

            if ($request->control_proceso_id) {
                $controlProceso = \App\Models\ControlProceso::where('id', $request->control_proceso_id)
                    ->where('estado', 'FINALIZADO')
                    ->first();

                if (!$controlProceso) {
                    return response()->json([
                        'message' => 'El control de proceso no existe o no esta finalizado'
                    ], 422);
                }

                $asignadoProceso = $controlProceso->lotes()->sum('cantidad_kg');
                $disponibleProceso = $controlProceso->cantidad_salida_kg;
                $cantidad = (float) $request->cantidad_kg;

                if ($asignadoProceso + $cantidad > $disponibleProceso) {
                    return response()->json([
                        'message' => 'La cantidad excede el disponible del proceso.',
                        'disponible' => max(0, $disponibleProceso - $asignadoProceso),
                    ], 422);
                }
            } else {
                $asignado  = (float) $cosecha->lotes()->sum('cantidad_kg');
                $capacidad = (float) ($cosecha->cantidad_kg ?? 0);
                $cantidad  = (float) $request->cantidad_kg;

                if ($asignado + $cantidad > $capacidad) {
                    return response()->json([
                        'message'  => 'La cantidad excede el disponible en la cosecha.',
                        'restante' => max(0, $capacidad - $asignado),
                    ], 422);
                }
            }

            $codigo = $request->codigo_lote ?: $this->makeCodigoLote($producto, $cosecha);

            $lote = new Lote($request->only([
                'tanque_id','producto_id','cantidad_kg','fecha_envasado','fecha_caducidad','tipo_envase'
            ]));
            $lote->cosecha_id = $cosecha->id;
            $lote->control_proceso_id = $request->control_proceso_id;
            $lote->codigo_lote = $codigo;
            $lote->save();

            $producto->increment('cantidad', $cantidad);

            return $lote->load(['producto','tanque','controlProceso']);
        });
    }

    public function update(Request $request, Lote $lote)
    {
        return DB::transaction(function () use ($request, $lote) {
            $cosecha = AcopioCosecha::findOrFail($lote->cosecha_id);

            $nuevoProducto = Producto::findOrFail($request->producto_id);
            if ((int) $nuevoProducto->tipo_id !== 3) {
                return response()->json(['message' => 'El producto debe ser de tipo 3.'], 422);
            }

            $cantidadNueva = (float) $request->cantidad_kg;
            $cantidadVieja = (float) $lote->cantidad_kg;

            if ($request->control_proceso_id) {
                $controlProceso = \App\Models\ControlProceso::where('id', $request->control_proceso_id)
                    ->where('estado', 'FINALIZADO')
                    ->first();

                if (!$controlProceso) {
                    return response()->json([
                        'message' => 'El control de proceso no existe o no esta finalizado'
                    ], 422);
                }

                $asignadoSinActual = (float) $controlProceso->lotes()
                    ->where('id', '!=', $lote->id)
                    ->sum('cantidad_kg');

                $disponible = $controlProceso->cantidad_salida_kg;
                if ($asignadoSinActual + $cantidadNueva > $disponible) {
                    return response()->json([
                        'message'  => 'La cantidad excede el disponible del proceso.',
                        'disponible' => max(0, $disponible - $asignadoSinActual),
                    ], 422);
                }
            } else {
                $asignadoSinActual = (float) $cosecha->lotes()
                    ->where('id', '!=', $lote->id)
                    ->sum('cantidad_kg');

                $capacidad = (float) ($cosecha->cantidad_kg ?? 0);
                if ($asignadoSinActual + $cantidadNueva > $capacidad) {
                    return response()->json([
                        'message'  => 'La cantidad excede el disponible en la cosecha.',
                        'restante' => max(0, $capacidad - $asignadoSinActual),
                    ], 422);
                }
            }

            $productoAnterior = Producto::findOrFail($lote->producto_id);
            $delta = $cantidadNueva - $cantidadVieja;

            if ($nuevoProducto->id !== $productoAnterior->id) {
                if ($cantidadVieja > 0) {
                    $productoAnterior->decrement('cantidad', $cantidadVieja);
                }
                if ($cantidadNueva > 0) {
                    $nuevoProducto->increment('cantidad', $cantidadNueva);
                }
            } else {
                if ($delta > 0) {
                    $nuevoProducto->increment('cantidad', $delta);
                } elseif ($delta < 0) {
                    $nuevoProducto->decrement('cantidad', abs($delta));
                }
            }

            $data = $request->only([
                'tanque_id','producto_id','cantidad_kg','fecha_envasado','fecha_caducidad','tipo_envase'
            ]);
            if ($request->filled('codigo_lote')) {
                $data['codigo_lote'] = $request->codigo_lote;
            }
            if ($request->has('control_proceso_id')) {
                $data['control_proceso_id'] = $request->control_proceso_id;
            }

            $lote->update($data);

            return $lote->load(['producto','tanque','controlProceso']);
        });
    }

    public function destroy(Lote $lote)
    {
        return DB::transaction(function () use ($lote) {
            $producto = Producto::find($lote->producto_id);
            if ($producto && (float) $lote->cantidad_kg > 0) {
                $producto->decrement('cantidad', (float) $lote->cantidad_kg);
            }
            $lote->delete();

            return response()->json(['deleted' => true]);
        });
    }

    private function makeCodigoLote(Producto $producto, AcopioCosecha $cosecha): string
    {
        // Formato: AAAAMMDD-PRODID-COSECHAID-SEC
        $base  = now()->format('Ymd') . '-' . $producto->id . '-' . $cosecha->id;
        $count = Lote::where('cosecha_id', $cosecha->id)->count() + 1;
        return sprintf('%s-%03d', $base, $count);
    }
}
