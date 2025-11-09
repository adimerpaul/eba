<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
class ProductoController extends Controller{
    public function index(Request $request)
    {
        
        $q = Producto::query()->where('id', '>', 0 );

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('nombre_producto', 'like', "%{$term}%")
                    ->orWhere('codigo_producto', 'like', "%{$term}%")
                    ->orWhere('codigo_barra', 'like', "%{$term}%");
            });
        }

        if ($request->filled('tipo')) {
            $q->where('tipo_id', (int) $request->get('tipo'));
        }

        $q->whereNull('deleted_at')->orderBy('nombre_producto');

        // Si quieres paginar, habilita esto:
        // $perPage = (int) ($request->get('per_page', 0));
        // if ($perPage > 0) return $q->paginate($perPage);

        return $q->get();
    }

    public function productoExcel(Request $request){
        if($request->tipo_id==0){
            $result = Producto::where('codigo_producto', 'like', "%{$request->search}%")
            ->orWhere('nombre_producto', 'like', "%{$request->search}%")->get();
            $nombre = 'TODOS';
        }
        else{
            $result = Producto::where('tipo_id', $request->tipo_id)
            ->where('codigo_producto', 'like', "%{$request->search}%")
            ->orWhere('nombre_producto', 'like', "%{$request->search}%")->get();
            $tipo = TipoProducto::find($request->tipo_id);
            $nombre = $tipo->nombre_tipo;
        }
       /// return $result;

          $template = storage_path('app/excel/productos.xlsx');
                $output   = public_path('reportes/reporte_producto.xlsx');

                // Cargar la plantilla
                $spreadsheet = IOFactory::load($template);
                $sheet = $spreadsheet->getActiveSheet();
                //$sheet->setCellValue("E1", $nombre);

                // Escribir datos (ejemplo a partir de fila 2)
                $fila = 7;
                $i=1;
                foreach ($result as $u) {
                    $sheet->setCellValue("B{$fila}", $u->i);
                    $sheet->setCellValue("C{$fila}", $u->codigo_producto);
                    $sheet->setCellValue("D{$fila}", $u->nombre_producto);
                    $sheet->setCellValue("E{$fila}", '');
                    $sheet->setCellValue("F{$fila}", $u->presentacion);
                    $sheet->setCellValue("G{$fila}", $u->cantidad);
                    $sheet->setCellValue("H{$fila}", $u->costo??0);
                    $sheet->setCellValue("I{$fila}", $u->precio??0);
                    $sheet->setCellValue("J{$fila}", $u->fecha_vencimiento??'');
                    $fila++;
                    $i++;
                }

                // Guardar en public/
                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                // Retornar link para descarga
                return response()->download($output);


    }
    function getByTipo($tipo){
        return Producto::where('tipo_id', $tipo)->where('id','>',0)->get();
    }
    public function show(Producto $producto)
    {
        return $producto->load('tipo');
    }
    public function store(Request $request)
    {
//        $data = $request->validated();
        $data = $request->all();
        if (!isset($data['presentacion'])) {
            $data['presentacion'] = 'PIEZA';
        }
        $producto = Producto::create($data);
        return response()->json($producto->fresh('tipo'), 201);
    }

    public function update(Request $request, Producto $producto)
    {
        $producto = Producto::find($request->id);
        $producto->update($request->all());
        return $producto->fresh('tipo');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }

    /**
     * Subida de imagen (guarda en storage/public/productos y setea el campo 'imagen')
     */
    public function uploadImage(Request $request, Producto $producto)
    {
        $request->validate([
            'file' => ['required','image','max:4096'] // 4 MB
        ]);

        $path = $request->file('file')->store('productos', 'public');
        $producto->update(['imagen' => $path]);

        return response()->json([
            'ok'       => true,
            'imagen'   => $path,
            'producto' => $producto->fresh()
        ]);
    }
}
