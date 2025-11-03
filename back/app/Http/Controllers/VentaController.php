<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Kardex;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Transporte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class VentaController extends Controller
{
    public function notaPdf(Venta $venta)
    {
        $venta->load([
            'cliente',
            'transporte',
            'detalles' => fn($q) => $q->with(['lote','producto'])->orderBy('id')
        ]);

        // Datos de cabecera para la vista
        $empresa = [
            'nombre' => 'EBA - Empresa Boliviana de Alimentos',
            'logo'   => public_path('images/eba_logo.png'), // pon tu logo aquí
            'dir'    => 'Av. Industrial s/n',
            'tel'    => '+591 2 123456',
            'ciudad' => 'La Paz - Bolivia',
        ];

        $pdf = Pdf::loadView('ventas.nota', [
            'venta'   => $venta,
            'empresa' => $empresa,
            'hoy'     => now(),
        ])->setPaper('a5', 'portrait'); // chico tipo "nota"
//
//        // stream en el navegador
        return $pdf->stream('nota-venta-'.$venta->id.'.pdf');
    }
    /**
     * Listado simple de ventas con búsqueda por cliente, factura o guía (opcional)
     * GET /ventas?q=texto&per_page=20&page=1
     */
    public function index(Request $request)
    {
        $q = Venta::query()
            ->with(['cliente:id,nombre_cliente', 'transporte:id,empresa,responsable,placa'])
            ->where('id', '>', 0)
            ->whereDate('fecha_venta', '>=', $request->inicio)
            ->whereDate('fecha_venta', '<=', $request->fin)
            ->whereNull('deleted_at')
            ->orderByDesc('id');

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('num_factura', 'like', "%{$term}%")
                    ->orWhere('guia_remision', 'like', "%{$term}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre_cliente','like',"%{$term}%"));
            });
        }

        $perPage = (int) $request->get('per_page', 0);
        if ($perPage > 0) return $q->paginate($perPage);
        return $q->get();
    }

    public function ventaExcel(Request $request){
        $result = $this->index($request);

                    $template = storage_path('app/excel/ventas.xlsx');
                $output   = public_path('reportes/reporte_venta.xlsx');

                // Cargar la plantilla
                $spreadsheet = IOFactory::load($template);
                $sheet = $spreadsheet->getActiveSheet();

                // Escribir datos (ejemplo a partir de fila 2)
                $fila = 7;
                $i=1;
                foreach ($result as $u) {
                    $sheet->setCellValue("B{$fila}", $i);
                    $sheet->setCellValue("C{$fila}", date('d/m/Y', strtotime($u->fecha_venta)));
                    $sheet->setCellValue("D{$fila}", $u->cliente['nombre_cliente']);
                    $sheet->setCellValue("E{$fila}", $u->num_factura);
                    $sheet->setCellValue("F{$fila}", $u->guia_remision);
                    $sheet->setCellValue("G{$fila}", $u->precio_total);
                    $fila++;
                    $i++;
                }

                // Guardar en public/
                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                // Retornar link para descarga
                return response()->download($output);
    }

    public function show(Venta $venta)
    {
        // Detalle: cliente, transporte y kardex (ítems)
        return $venta->load([
            'cliente:id,nombre_cliente,nit',
            'transporte:id,empresa,responsable,placa',
            'detalles' => function ($d) {
                $d->with(['lote:id,codigo_lote','producto:id,nombre_producto'])
                    ->orderBy('id');
            }
        ]);
    }

    /**
     * Crea una venta y sus movimientos en Kardex (salida)
     * Body esperado:
     * {
     *   cliente_id, transporte_id, fecha_venta?, destino_final?, guia_remision?, num_factura?,
     *   detalles: [{ lote_id, producto_id, cantidad_salida, precio_venta }]
     * }
     * user_id se obtiene de $request->user()->id
     */
    public function store(Request $request)
    {
        // Validaciones básicas inline
        $validated = $request->validate([
            'cliente_id'                => ['required','exists:clientes,id'],
            'transporte_id'             => ['required','exists:transportes,id'],
            'fecha_venta'               => ['nullable','date'],
            'destino_final'             => ['nullable','string','max:200'],
            'guia_remision'             => ['nullable','string','max:100'],
            'num_factura'               => ['nullable','string','max:20'],

            'detalles'                  => ['required','array','min:1'],
            'detalles.*.lote_id'        => ['required','exists:lotes,id'],
            'detalles.*.producto_id'    => ['required','exists:productos,id'],
            'detalles.*.cantidad_salida'=> ['required','numeric','min:0.001'],
            'detalles.*.precio_venta'   => ['required','numeric','min:0'],
        ]);

        $user = $request->user(); // <- importante
        if (!$user) {
            throw ValidationException::withMessages(['user' => 'Usuario no autenticado']);
        }

        return DB::transaction(function () use ($validated, $user) {
            // Crear cabecera de venta
            $venta = new Venta();
            $venta->cliente_id     = $validated['cliente_id'];
            $venta->transporte_id  = $validated['transporte_id'];
            $venta->fecha_venta    = $validated['fecha_venta'] ?? now();
            $venta->destino_final  = $validated['destino_final'] ?? null;
            $venta->guia_remision  = $validated['guia_remision'] ?? null;
            $venta->num_factura    = $validated['num_factura'] ?? null;
            $venta->precio_total   = 0; // se recalcula más abajo
            $venta->save();

            // Para evitar N consultas por disponible, cachear sumas de kardex por lote
            $detalles = $validated['detalles'];
            $loteIds  = array_values(array_unique(array_column($detalles, 'lote_id')));

            // Mapear lotes y sus disponibles actuales
            $lotes = Lote::query()
                ->with('producto:id,nombre_producto,cantidad_kg')
                ->whereIn('id', $loteIds)
                ->get()
                ->keyBy('id');

            $disponibles = $this->disponiblesPorLote($loteIds); // [lote_id => disponible_kg]

            $total = 0;

            foreach ($detalles as $i => $d) {
                $loteId     = (int) $d['lote_id'];
                $prodId     = (int) $d['producto_id'];
                $cantSalida = (float) $d['cantidad_salida'];
                $precio     = (float) $d['precio_venta'];

                $lote = $lotes->get($loteId);
                if (!$lote) {
                    throw ValidationException::withMessages([
                        "detalles.$i.lote_id" => "Lote no encontrado"
                    ]);
                }

                // Validar que el producto del detalle coincida con el del lote
                if ((int) $lote->producto_id !== $prodId) {
                    throw ValidationException::withMessages([
                        "detalles.$i.producto_id" => "El producto no coincide con el del lote seleccionado"
                    ]);
                }

                // Disponibilidad actualizada (considera lo que ya agregaste en este loop)
                $dispActual = $disponibles[$loteId] ?? (float) $lote->cantidad_kg;
                if ($cantSalida > $dispActual) {
                    throw ValidationException::withMessages([
                        "detalles.$i.cantidad_salida" => "Cantidad excede disponible. Disponible: ".$this->fmt($dispActual)." kg"
                    ]);
                }

                // Crear movimiento en kardex (salida)
                Kardex::create([
                    'lote_id'            => $loteId,
                    'producto_id'        => $prodId,
                    'venta_id'           => $venta->id,
                    'fecha_registro'     => now(),
                    'cantidad_procesada' => 0, // para ventas no aplica
                    'cantidad_salida'    => $cantSalida,
                    'precio_venta'       => $precio,
                    'user_id'            => $user->id,
                ]);

                // Descontar del disponible "en memoria" para futuras iteraciones del mismo lote
                $disponibles[$loteId] = $dispActual - $cantSalida;

                // Descontar stock del producto
                $producto = Producto::find($prodId);
                if ($producto) {
                    // Evitar negativos accidentales
                    $nuevoStock = max(0, (float) ($producto->cantidad_kg ?? 0) - $cantSalida);
                    $producto->update(['cantidad_kg' => $nuevoStock]);
                }

                $total += $cantSalida * $precio;
            }

            // Actualizar total
            $venta->update(['precio_total' => $total]);

            // Responder con las relaciones útiles
            return $venta->fresh()->load([
                'cliente:id,nombre_cliente,nit',
                'transporte:id,empresa,responsable,placa',
                'detalles' => fn($d) => $d->with(['lote:id,codigo_lote','producto:id,nombre_producto'])
            ]);
        });
    }

    public function destroy(Venta $venta)
    {
        // Nota: revertir kardex/stock está fuera de alcance aquí. Si lo necesitas, puedo dejarte restore reversible.
        $venta->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Endpoint para el selector de lotes con su disponible (kg)
     * GET /lotes/disponibles?q=texto
     * Devuelve: id, codigo_lote, producto_id, producto_nombre, disponible_kg
     */
    public function lotesDisponibles(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        // Subquery: suma de salidas por lote (ignorando soft-deletes)
        $sub = Kardex::query()
            ->selectRaw('lote_id, COALESCE(SUM(cantidad_salida),0) AS salidas')
            ->whereNull('deleted_at')
            ->groupBy('lote_id');

        $q = Lote::query()
            ->leftJoinSub($sub, 'k', 'k.lote_id', '=', 'lotes.id')
            ->leftJoin('productos', 'productos.id', '=', 'lotes.producto_id')
            ->select([
                'lotes.id',
                'lotes.codigo_lote',
                'lotes.producto_id',
                DB::raw("COALESCE(productos.nombre_producto, '') AS producto_nombre"),
                DB::raw('(COALESCE(lotes.cantidad_kg,0) - COALESCE(k.salidas,0)) AS disponible_kg'),
            ]);

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('lotes.codigo_lote', 'like', "%{$term}%")
                    ->orWhere('productos.nombre_producto', 'like', "%{$term}%");
            });
        }

        // ⚠️ En Postgres, NO usar HAVING aquí. Usar WHERE sobre la expresión calculada.
        $rows = $q->whereRaw('(COALESCE(lotes.cantidad_kg,0) - COALESCE(k.salidas,0)) > 0')
            ->orderBy('lotes.id', 'desc')
            ->limit(50)
            ->get();

        return $rows;
    }

    // ===================== Helpers =====================

    /** Calcula disponibles por lote en un solo query. Retorna [lote_id => disponible_kg] */
    private function disponiblesPorLote(array $loteIds): array
    {
        if (empty($loteIds)) return [];

        $sub = Kardex::query()
            ->selectRaw('lote_id, COALESCE(SUM(cantidad_salida),0) AS salidas')
            ->whereIn('lote_id', $loteIds)
            ->groupBy('lote_id');

        $rows = Lote::query()
            ->leftJoinSub($sub, 'k', 'k.lote_id', '=', 'lotes.id')
            ->whereIn('lotes.id', $loteIds)
            ->select([
                'lotes.id',
                DB::raw('COALESCE(lotes.cantidad_kg - COALESCE(k.salidas,0), 0) AS disponible_kg')
            ])
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r->id] = (float)$r->disponible_kg;
        }
        return $out;
    }

    private function fmt($v): string
    {
        return number_format((float)$v, 2, '.', '');
    }
}
