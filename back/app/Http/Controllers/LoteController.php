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

    /**
     * Trazabilidad completa de un lote por código
     * GET /lotes/trazabilidad?codigo_lote=XXXXXXXX
     */
    public function trazabilidad(Request $request)
    {
        $codigoLote = $request->input('codigo_lote');
        
        if (!$codigoLote) {
            return response()->json(['message' => 'Código de lote requerido'], 400);
        }

        $lote = Lote::with(['producto', 'tanque', 'controlProceso'])
            ->where('codigo_lote', $codigoLote)
            ->first();

        if (!$lote) {
            return response()->json(['message' => 'Lote no encontrado'], 404);
        }

        $trazabilidad = DB::selectOne("
            SELECT 
                l.id as lote_id,
                l.codigo_lote,
                l.fecha_envasado,
                p.nombre as producto,
                l.cantidad_kg,
                COALESCE(k.stock_actual, 0) as stock_actual,
                COALESCE(k.cantidad_vendida, 0) as cantidad_vendida
            FROM traza.lotes l
            LEFT JOIN traza.productos p ON p.id = l.producto_id
            LEFT JOIN (
                SELECT 
                    lote_id,
                    SUM(CASE WHEN movimiento = 'ENTRADA' THEN cantidad_kg ELSE -cantidad_kg END) as stock_actual,
                    SUM(CASE WHEN movimiento = 'SALIDA' AND tipo_movimiento = 'VENTA' THEN cantidad_kg ELSE 0 END) as cantidad_vendida
                FROM traza.kardex
                GROUP BY lote_id
            ) k ON k.lote_id = l.id
            WHERE l.codigo_lote = ?
        ", [$codigoLote]);

        $productores = DB::select("
            SELECT DISTINCT
                pr.nombre_completo as nombre,
                pr.codigo_runsa as runsa,
                STRING_AGG(DISTINCT a.nombre, ', ') as apiarios,
                MIN(ac.fecha_cosecha) as fecha_cosecha,
                SUM(ac.cantidad_kg) as cantidad_kg
            FROM traza.lotes l
            INNER JOIN traza.acopio_cosechas ac ON ac.id = l.cosecha_id
            INNER JOIN traza.apiarios a ON a.id = ac.apiario_id
            INNER JOIN traza.productores pr ON pr.id = a.productor_id
            WHERE l.codigo_lote = ?
            GROUP BY pr.id, pr.nombre_completo, pr.codigo_runsa
        ", [$codigoLote]);

        $proceso = null;
        if ($lote->controlProceso) {
            $proceso = DB::selectOne("
                SELECT 
                    t.nombre as tanque,
                    cp.metodo_proceso,
                    cp.fecha_inicio,
                    cp.fecha_fin,
                    cp.temperatura,
                    cp.merma_porcentaje
                FROM traza.control_procesos cp
                LEFT JOIN traza.tanques t ON t.id = cp.tanque_id
                WHERE cp.id = ?
            ", [$lote->controlProceso->id]);
        }

        $ventas = DB::select("
            SELECT 
                v.id as venta_id,
                v.fecha as fecha_venta,
                c.nombre_completo as comprador,
                k.cantidad_kg,
                vd.precio_unitario * vd.cantidad as monto_total,
                v.lugar_destino
            FROM traza.kardex k
            INNER JOIN traza.ventas v ON v.id = k.venta_id
            INNER JOIN traza.venta_detalles vd ON vd.venta_id = v.id AND vd.lote_id = k.lote_id
            LEFT JOIN traza.clientes c ON c.id = v.cliente_id
            WHERE k.lote_id = ? 
            AND k.movimiento = 'SALIDA' 
            AND k.tipo_movimiento = 'VENTA'
            ORDER BY v.fecha DESC
        ", [$lote->id]);

        return response()->json([
            'lote_id' => $trazabilidad->lote_id,
            'codigo_lote' => $trazabilidad->codigo_lote,
            'fecha_envasado' => $trazabilidad->fecha_envasado,
            'producto' => $trazabilidad->producto,
            'cantidad_kg' => $trazabilidad->cantidad_kg,
            'stock_actual' => $trazabilidad->stock_actual,
            'cantidad_vendida' => $trazabilidad->cantidad_vendida,
            'productores' => $productores,
            'proceso' => $proceso,
            'ventas' => $ventas
        ]);
    }

    private function makeCodigoLote(Producto $producto, AcopioCosecha $cosecha): string
    {
        // Formato: AAAAMMDD-PRODID-COSECHAID-SEC
        $base  = now()->format('Ymd') . '-' . $producto->id . '-' . $cosecha->id;
        $count = Lote::where('cosecha_id', $cosecha->id)->count() + 1;
        return sprintf('%s-%03d', $base, $count);
    }
}
