<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use App\Models\Productor;
use App\Models\Lote;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AcopioCosechaController extends Controller{
    public function resumenMensual(Request $request)
    {
        $year = $request->get('year', now()->year);

        $rows = AcopioCosecha::selectRaw("EXTRACT(MONTH FROM fecha_cosecha)::int as mes, SUM(cantidad_kg) as total_kg")
            ->whereYear('fecha_cosecha', $year)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $labels = [
            'Enero', 'Febrero', 'Marzo', 'Abril',
            'Mayo', 'Junio', 'Julio', 'Agosto',
            'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $row = $rows->firstWhere('mes', $i);
            $data[] = $row ? (float) $row->total_kg : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
            'year'   => (int) $year,
        ]);
    }

    public function resumenPorProducto(Request $request)
    {
        $year = $request->get('year'); // opcional

        $query = AcopioCosecha::query()
            ->join('productos', 'acopio_cosechas.producto_id', '=', 'productos.id')
            ->selectRaw('productos.nombre_producto as producto, SUM(acopio_cosechas.cantidad_kg) as total_kg');

        if ($year) {
            $query->whereYear('acopio_cosechas.fecha_cosecha', $year);
        }

        $rows = $query
            ->groupBy('productos.nombre_producto')
            ->orderBy('productos.nombre_producto')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('producto'),
            'data'   => $rows->pluck('total_kg')->map(fn ($v) => (float) $v),
        ]);
    }


    public function showByQr(string $code)
    {
        /*$acopio = AcopioCosecha::query()
            ->with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->where('qr_code', $code)
            ->first();

        if (!$acopio) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($acopio);*/
        /*$lote = Lote::query()
            ->with(['apio_cosecha.apiario.productor', 'apio_cosecha', 'producto'])
            ->where('codigo_lote', $code)
            ->first();

        if (!$lote) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($lote);*/

        $res=DB::SELECT("SELECT * FROM traza.v_trazabilidad_lote WHERE codigo_lote='$code'");
        //return $res;
        if ($res)
        {
            return $res[0];
        }
        else
        {
            return response()->json(['message' => 'No encontrado'], 404);
        }
    }
    function index(Request $request){
        //return $request;
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $estado = $request->input('estado');
        $productor_id = $request->input('productor_id');
        $producto_id = $request->input('producto_id');
        $departamento_id = $request->input('departamento_id');
        $municipio_id = $request->input('municipio_id');
        //return $request;
        if( $fecha_inicio && $fecha_fin){
            $acopiosCosechas = AcopioCosecha::whereBetween('fecha_cosecha', [$fecha_inicio, $fecha_fin])->with('apiario.productor','producto');
        }
        if($estado){
            $acopiosCosechas = $acopiosCosechas->where('estado', $estado);
        }
        if($request->num_acta != null || $request->num_acta != ''){
            $acopiosCosechas = $acopiosCosechas->where('num_acta', $request->num_acta);
        }
        if($productor_id){
            // se tiene los apiarios relacionados con el productor
            $productor = Productor::with('apiarios')->where('id', $productor_id)->first();
            $apiarios = $productor->apiarios->pluck('id')->toArray();
            $acopiosCosechas = $acopiosCosechas->whereIn('apiario_id', $apiarios);
            //$acopiosCosechas = $acopiosCosechas->where('productor_id', $productor_id);
        }
        if($municipio_id){
            $acopiosCosechas = $acopiosCosechas->whereHas('apiario', function ($query) use ($municipio_id) {
                $query->where('municipio_id', $municipio_id);
            });
        }

         $acopiosCosechas = $acopiosCosechas->get();
        return $acopiosCosechas;
    }

    public function productorAcopios(Request $request){
        $productor_id = $request->input('productor_id');
        // recuerar  los apiarios del productor
        $productor = Productor::with('apiarios')->where('id', $productor_id)->first();
        $apiarios = $productor->apiarios->pluck('id')->toArray();
        $acopiosCosechas = AcopioCosecha::whereIn('apiario_id', $apiarios)->with('apiario.productor','producto')->get();
        return $acopiosCosechas;
    }

    public function acopioExcel(Request $request) {
        $params = $request->all();
        $resultado = $this->index(new Request($params));

        $template = storage_path('app/excel/acopio.xlsx');
        $output   = public_path('reportes/reporte_acopios.xlsx');

        // Cargar la plantilla
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        // Escribir datos (ejemplo a partir de fila 2)
        $fila = 7;
        $i=1;
        foreach ($resultado as $u) {
            $sheet->setCellValue("B{$fila}", $i);
            $sheet->setCellValue("C{$fila}", $u->fecha_cosecha);
            $sheet->setCellValue("D{$fila}", $u->apiario->productor['nombre_completo']);
            $sheet->setCellValue("E{$fila}", $u->cantidad_kg);
            $sheet->setCellValue("F{$fila}", $u->humedad);
            $sheet->setCellValue("G{$fila}", $u->temperatura_almacenaje);
            $sheet->setCellValue("H{$fila}", $u->num_act);
            $sheet->setCellValue("I{$fila}", $u->observaciones);
            $sheet->setCellValue("J{$fila}", $u->estado);
            $fila++;
            $i++;
        }
                        // Guardar en public/
                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                // Retornar link para descarga
                return response()->download($output);

    }
    public function show($id)
    {
        // MODIFICACION 2025-11-18: Agregar relaciones de provincia y departamento para encabezados de formularios
        return AcopioCosecha::with([
            'apiario.productor.municipio.provincia',
            'apiario.productor.municipio.departamento',
            'apiario.productor',
            'apiario.municipio',
            'producto'
        ])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $qr_code = uniqid();
        error_log(json_encode($request->all()));
        $request->merge(['qr_code' => $qr_code]);
        $acopio = AcopioCosecha::create($request->all());
        return response()->json($acopio->fresh(['apiario.productor','apiario.municipio']), 201);
    }

    public function update(Request $request, $id)
    {
        $request->except(['qr_code']);
        $acopio = AcopioCosecha::findOrFail($id);
        $acopio->update($request->all());
        return $acopio->fresh(['apiario.productor','apiario.municipio']);
    }

    public function destroy($id)
    {
        $acopio = AcopioCosecha::findOrFail($id);
        $acopio->delete();
        return response()->json(['deleted' => true]);
    }
}
