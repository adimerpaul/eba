<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
class AcopioCosechaController extends Controller{
    public function showByQr(string $code)
    {
        $acopio = AcopioCosecha::query()
            ->with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->where('qr_code', $code)
            ->first();

        if (!$acopio) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($acopio);
    }
    function index(Request $request){
        //return $request;
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $estado = $request->input('estado');
        $productor_id = $request->input('productor_id');
        $producto_id = $request->input('producto_id');
        $acopiosCosechas = AcopioCosecha::whereBetween('fecha_cosecha', [$fecha_inicio, $fecha_fin])->with('apiario.productor','producto');
        if($estado){
            $acopiosCosechas = $acopiosCosechas->where('estado', $estado);
        }
        if($request->num_acta != null || $request->num_acta != ''){
            $acopiosCosechas = $acopiosCosechas->where('num_acta', $request->num_acta);
        }
        if($productor_id){
            // se tiene los apiarios relacionados con el productor
            $productor = \App\Models\Productor::find($productor_id);
            $apiarios = $productor->apiarios->pluck('id')->toArray();
            $acopiosCosechas = $acopiosCosechas->whereIn('apiario_id', $apiarios);
            //$acopiosCosechas = $acopiosCosechas->where('productor_id', $productor_id);
        }
        if($producto_id){
            $acopiosCosechas = $acopiosCosechas->where('producto_id', $producto_id);
        }
        
        $acopiosCosechas = $acopiosCosechas->get();
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
        return AcopioCosecha::with(['apiario.productor','apiario.municipio','producto'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $qr_code = uniqid();
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
