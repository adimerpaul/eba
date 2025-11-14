<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use App\Models\Organizacion; 
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Agregado para consultas SQL directas
class ProductorController extends Controller
{
    public function index(Request $request)
    {
        $q = Productor::query()
            ->with([
                'municipio:id,nombre_municipio,provincia_id,departamento_id'
            ])->with('organizacion');

        // Búsqueda libre MEJORADA
        // MODIFICACIÓN 2025-11-13: Agregada búsqueda por nombre completo concatenado
        // para permitir búsquedas como "ZUNY MARIA" que encuentre "ZUNY MARIA HURTADO"
        if($request->productor_id){
            $q->where('id', $request->productor_id);
        }
        if ($search = trim((string) $request->get('search', ''))) {
            // Normalizar búsqueda: eliminar espacios extras entre palabras
            $searchNormalized = preg_replace('/\s+/', ' ', $search);
            
            $q->where(function ($s) use ($search, $searchNormalized) {
                // NUEVO: Búsqueda por nombre completo concatenado (nombre + apellidos)
                // Esto permite buscar "ZUNY MARIA" y encontrar registros donde 
                // nombre="ZUNY" y apellidos="MARIA HURTADO PADILLA"
                $s->whereRaw("CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellidos,'')) ILIKE ?", ["%{$searchNormalized}%"])
                  
                  // Búsquedas originales mantenidas para compatibilidad
                  ->orWhere('runsa', 'ilike', "%{$search}%")
                  ->orWhere('sub_codigo', 'ilike', "%{$search}%")
                  ->orWhere('nombre', 'ilike', "%{$search}%")
                  ->orWhere('apellidos', 'ilike', "%{$search}%")
                  ->orWhere('numcarnet', 'ilike', "%{$search}%")
                  ->orWhere('comunidad', 'ilike', "%{$search}%")
                  ->orWhere('proveedor', 'ilike', "%{$search}%")
                  ->orWhere('cip_acopio', 'ilike', "%{$search}%")
                  ->orWhere('num_celular', 'ilike', "%{$search}%");
            });
        }
        
        /* CÓDIGO ORIGINAL ANTES DE LA MEJORA (comentado para referencia):
        if ($search = trim((string) $request->get('search', ''))) {
            $q->where(function ($s) use ($search) {
                $s->where('runsa', 'ilike', "%{$search}%")
                    ->orWhere('sub_codigo', 'ilike', "%{$search}%")
                    ->orWhere('nombre', 'ilike', "%{$search}%")
                    ->orWhere('apellidos', 'ilike', "%{$search}%")
                    ->orWhere('numcarnet', 'ilike', "%{$search}%")
                    ->orWhere('comunidad', 'ilike', "%{$search}%")
                    ->orWhere('proveedor', 'ilike', "%{$search}%")
                    ->orWhere('cip_acopio', 'ilike', "%{$search}%")
                    ->orWhere('num_celular', 'ilike', "%{$search}%");
            });
        }
        */

        // Filtros administrativos
        if ($depId = $request->get('departamento_id')) {
            $q->whereHas('municipio', fn($m) => $m->where('departamento_id', $depId));
        }
        if ($provId = $request->get('provincia_id')) {
            $q->whereHas('municipio', fn($m) => $m->where('provincia_id', $provId));
        }
        if ($munId = $request->get('municipio_id')) {
            $q->where('municipio_id', $munId);
        }

        // Otros filtros
        if ($estado = $request->get('estado')) {
            $q->where('estado', $estado);
        }
        if ($sexo = $request->get('sexo')) {
            $q->where('sexo', (int) $sexo);
        }
        if ($orgId = $request->get('organizacion_id')) {
            $q->where('organizacion_id', $orgId);
        }

        // Rango de fechas (registro)
        if ($fd = $request->get('fecha_desde')) {
            $q->whereDate('fecha_registro', '>=', $fd);
        }
        if ($fh = $request->get('fecha_hasta')) {
            $q->whereDate('fecha_registro', '<=', $fh);
        }

        // Ordenación segura
        $allowedSorts = [
            'id','runsa','sub_codigo','nombre','apellidos','numcarnet','fecha_registro','estado'
        ];
        $sortBy = $request->get('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'id';

        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $q->orderBy($sortBy, $sortDir);

        // Paginación (remota)
        $perPage = max(10, min((int) $request->get('per_page', 50), 200)); // 100k registros → paginar 50-100
        return $q->paginate($perPage)->appends($request->query());
    }


    public function productorExcel(Request $request) {
            $params = $request->all();

            $pagina = 1;
            $todos = collect();

            do {
                // asegura pasar todos los filtros + la página actual
                $params['page'] = $pagina;
                $paginado = $this->index(new Request($params)); // usa tu función existente

                // getCollection() devuelve una Collection con los items de esta página
                $todos = $todos->merge($paginado->getCollection());

                $lastPage = $paginado->lastPage(); // número total de páginas
                $pagina++;
            } while ($pagina <= $lastPage);

            $template = storage_path('app/excel/proveedores.xlsx');
                $output   = public_path('reportes/reporte_proveedor.xlsx');

                // Cargar la plantilla
                $spreadsheet = IOFactory::load($template);
                $sheet = $spreadsheet->getActiveSheet();

                // Escribir datos (ejemplo a partir de fila 2)
                $fila = 7;
                foreach ($todos as $u) {
                    $sheet->setCellValue("B{$fila}", $u->id);
                    $sheet->setCellValue("C{$fila}", $u->runsa);
                    $sheet->setCellValue("D{$fila}", $u->sub_codigo);
                    $sheet->setCellValue("E{$fila}", $u->municipio['nombre_municipio']);
                    $sheet->setCellValue("F{$fila}", $u->nombre_completo);
                    $sheet->setCellValue("G{$fila}", $u->numcarnet);
                    $sheet->setCellValue("H{$fila}", $u->num_celular);
                    $sheet->setCellValue("I{$fila}", $u->comunidad??'');
                    $sheet->setCellValue("J{$fila}", $u->estado);
                    $sheet->setCellValue("K{$fila}", $u->fecha_registro);
                    $fila++;
                }

                // Guardar en public/
                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                // Retornar link para descarga
                return response()->download($output);

            //return $todos;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            //'municipio_id'   => ['nullable','integer','exists:municipios,id'],
            'runsa'          => ['nullable','string','max:20'],
            'sub_codigo'     => ['nullable','string','max:20'],
            'nombre'         => ['required','string','max:200'],
            'apellidos'      => ['required','string','max:200'],
            'numcarnet'      => ['required','string','max:20'],
            'expedido'       => ['nullable','string','max:10'],
            'fec_nacimiento' => ['nullable','date'],
            'sexo'           => ['nullable','integer','in:1,2'],
            'direccion'      => ['nullable','string'],
            'comunidad'      => ['nullable','string','max:100'],
            'proveedor'      => ['nullable','string','max:50'],
            'cip_acopio'     => ['nullable','string','max:50'],
            'num_celular'    => ['nullable','string','max:15'],
            'ocupacion'      => ['nullable','string','max:100'],
            'otros'          => ['nullable','string'],
            'seleccion'      => ['nullable','integer','min:0'],
            'organizacion_id'=> ['nullable','integer','exists:organizaciones,id'],
            'fecha_registro' => ['nullable','date'],
            'fecha_expiracion'=>['nullable','date'],
            'estado'         => ['nullable','string','in:VIGENTE,VENCIDO,INACTIVO,ACTIVO'],
        ]);
        $organizacion = Organizacion::find($data['organizacion_id']);
        $data['ocupacion']='APICULTOR';
        //$data['organizacion_id'] = $organizacion->id;
        $data['municipio_id'] = $organizacion->municipio_id;

        $p = Productor::create($data);
        return response()->json($p->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza']), 201);
    }

    public function show(Productor $productor)
    {
        return $productor->load([
            'municipio:id,nombre_municipio,provincia_id,departamento_id',
            'organizacion',
            'certificaciones',
            'apiarios.colmenas.tipoMiel'
        ]);
    }

    public function update(Request $request, Productor $productor)
    {
        $data = $request->validate([
            //'municipio_id'   => ['nullable','integer','exists:municipios,id'],
            'runsa'          => ['nullable','string','max:20'],
            'sub_codigo'     => ['nullable','string','max:20'],
            'nombre'         => ['required','string','max:200'],
            'apellidos'      => ['required','string','max:200'],
            'numcarnet'      => ['required','string','max:20'],
            'expedido'       => ['nullable','string','max:10'],
            'fec_nacimiento' => ['nullable','date'],
            'sexo'           => ['nullable','integer','in:1,2'],
            'direccion'      => ['nullable','string'],
            'comunidad'      => ['nullable','string','max:100'],
            'proveedor'      => ['nullable','string','max:50'],
            'cip_acopio'     => ['nullable','string','max:50'],
            'num_celular'    => ['nullable','string','max:15'],
            'ocupacion'      => ['nullable','string','max:100'],
            'otros'          => ['nullable','string'],
            'seleccion'      => ['nullable','integer','min:0'],
            'organizacion_id'=> ['nullable','integer','exists:organizaciones,id'],
            'fecha_registro' => ['nullable','date'],
            'fecha_expiracion'=>['nullable','date'],
            'estado'         => ['nullable','string','in:VIGENTE,VENCIDO,INACTIVO,ACTIVO'],
        ]);

        $organizacion = Organizacion::find($data['organizacion_id']);
        //$data['organizacion_id'] = $organizacion->id;
        $data['municipio_id'] = $organizacion->municipio_id;

        $productor->update($data);
        return $productor->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza']);
    }

    public function destroy(Productor $productor)
    {
        $productor->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }

    /**
     * Obtiene el reporte de acopios mensuales de un productor
     * en un período de gestión apícola (julio a junio)
     * 
     * @param Request $request
     * @param int $productorId
     * @return \Illuminate\Http\JsonResponse
     * 
     * Ejemplo de uso:
     * GET /api/productores/11613/acopios-gestion?gestion=2025&producto_id=1
     * 
     * Retorna datos agrupados por mes en el orden: Jul, Ago, Sep, Oct, Nov, Dic, Ene, Feb, Mar, Abr, May, Jun
     */
    public function acopiosMensualesGestion(Request $request, $productorId)
    {
        // Validar parámetros de entrada
        $request->validate([
            'gestion' => 'required|integer|min:2020|max:2030',
            'producto_id' => 'nullable|integer|exists:productos,id'
        ]);

        $gestion = (int) $request->input('gestion');
        $productoId = $request->input('producto_id', 1); // default: 1 = miel

        // Calcular rango de fechas para la gestión apícola (julio a junio)
        $fechaInicio = "{$gestion}-07-01";
        $fechaFin = ($gestion + 1) . "-06-30";

        // Obtener el productor con validación
        $productor = Productor::findOrFail($productorId);
        
        // Obtener todos los apiarios del productor
        $apiariosIds = $productor->apiarios()->pluck('id')->toArray();

        // Si no tiene apiarios, retornar estructura vacía con meses en cero
        if (empty($apiariosIds)) {
            return response()->json([
                'productor' => $productor->nombre_completo,
                'productor_id' => $productor->id,
                'gestion' => $gestion,
                'producto_id' => $productoId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'meses' => $this->getMesesVaciosGestion(),
                'total_kg' => 0,
                'total_entregas' => 0,
                'promedio_entrega' => 0
            ]);
        }

        // Query SQL para obtener acopios agrupados por mes de gestión
        // Usa generate_series para garantizar que todos los meses aparezcan (incluso sin datos)
        $resultados = DB::select("
            WITH meses_gestion AS (
                -- Generar los 12 meses de la gestión (julio=0 hasta junio=11)
                SELECT 
                    gs AS offset_mes,
                    CASE gs
                        WHEN 0 THEN 'Julio'
                        WHEN 1 THEN 'Agosto'
                        WHEN 2 THEN 'Septiembre'
                        WHEN 3 THEN 'Octubre'
                        WHEN 4 THEN 'Noviembre'
                        WHEN 5 THEN 'Diciembre'
                        WHEN 6 THEN 'Enero'
                        WHEN 7 THEN 'Febrero'
                        WHEN 8 THEN 'Marzo'
                        WHEN 9 THEN 'Abril'
                        WHEN 10 THEN 'Mayo'
                        WHEN 11 THEN 'Junio'
                    END AS mes_nombre,
                    -- Calcular año correspondiente para cada mes
                    CASE 
                        WHEN gs <= 5 THEN :gestion
                        ELSE :gestion + 1
                    END AS anio_mes
                FROM generate_series(0, 11) gs
            ),
            acopios_gestion AS (
                -- Obtener acopios en el rango de la gestión
                SELECT 
                    ac.id,
                    ac.fecha_cosecha,
                    ac.cantidad_kg,
                    ac.apiario_id,
                    -- Calcular el offset del mes según la gestión apícola
                    -- Julio (mes 7) = offset 0, Junio (mes 6) = offset 11
                    CASE 
                        WHEN EXTRACT(MONTH FROM ac.fecha_cosecha) >= 7 THEN 
                            EXTRACT(MONTH FROM ac.fecha_cosecha) - 7
                        ELSE 
                            EXTRACT(MONTH FROM ac.fecha_cosecha) + 5
                    END AS offset_mes
                FROM acopio_cosechas ac
                WHERE ac.apiario_id = ANY(:apiarios_ids::int[])
                  AND ac.producto_id = :producto_id
                  AND ac.fecha_cosecha >= :fecha_inicio::date
                  AND ac.fecha_cosecha <= :fecha_fin::date
                  AND ac.deleted_at IS NULL
            )
            SELECT 
                mg.offset_mes,
                mg.mes_nombre,
                mg.anio_mes,
                COALESCE(SUM(ag.cantidad_kg), 0)::numeric(10,2) AS cantidad_kg,
                COUNT(ag.id) AS num_entregas
            FROM meses_gestion mg
            LEFT JOIN acopios_gestion ag ON mg.offset_mes = ag.offset_mes
            GROUP BY mg.offset_mes, mg.mes_nombre, mg.anio_mes
            ORDER BY mg.offset_mes
        ", [
            'gestion' => $gestion,
            'apiarios_ids' => '{' . implode(',', $apiariosIds) . '}',
            'producto_id' => $productoId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);

        // Calcular totales
        $totalKg = array_sum(array_map(fn($m) => (float)$m->cantidad_kg, $resultados));
        $totalEntregas = array_sum(array_map(fn($m) => (int)$m->num_entregas, $resultados));
        $promedioEntrega = $totalEntregas > 0 ? round($totalKg / $totalEntregas, 2) : 0;

        // Retornar respuesta estructurada
        return response()->json([
            'productor' => $productor->nombre_completo,
            'productor_id' => $productor->id,
            'runsa' => $productor->runsa,
            'municipio' => $productor->municipio->nombre_municipio ?? null,
            'organizacion' => $productor->organizacion->nombre_organiza ?? null,
            'gestion' => $gestion,
            'producto_id' => $productoId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'meses' => $resultados,
            'total_kg' => round($totalKg, 2),
            'total_entregas' => $totalEntregas,
            'promedio_entrega' => $promedioEntrega
        ]);
    }

    /**
     * Retorna estructura de meses vacía (sin acopios)
     * Útil cuando el productor no tiene apiarios
     */
    private function getMesesVaciosGestion()
    {
        $meses = [
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'
        ];
        
        $resultado = [];
        foreach ($meses as $index => $nombre) {
            $resultado[] = (object)[
                'offset_mes' => $index,
                'mes_nombre' => $nombre,
                'anio_mes' => $index <= 5 ? null : null,
                'cantidad_kg' => '0.00',
                'num_entregas' => 0
            ];
        }
        
        return $resultado;
    }

    /**
     * Exporta a Excel el reporte de acopios mensuales por gestión de un productor
     * Usa la misma lógica del método acopiosMensualesGestion() pero genera archivo Excel
     * 
     * @param Request $request
     * @param int $productorId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function acopiosGestionExcel(Request $request, $productorId)
    {
        // Validar parámetros
        $request->validate([
            'gestion' => 'required|integer|min:2020|max:2030',
            'producto_id' => 'nullable|integer|exists:productos,id'
        ]);

        $gestion = (int) $request->input('gestion');
        $productoId = $request->input('producto_id', 1);

        // Obtener datos usando el mismo método de consulta
        $response = $this->acopiosMensualesGestion($request, $productorId);
        $datos = $response->getData();

        // Cargar plantilla base (reutilizamos la de proveedores o creamos una simple)
        // Si no existe plantilla, creamos Excel desde cero
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ENCABEZADO
        $sheet->setCellValue('A1', 'REPORTE DE ACOPIOS POR GESTIÓN');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // DATOS DEL PRODUCTOR
        $fila = 3;
        $sheet->setCellValue("A{$fila}", 'Productor:');
        $sheet->setCellValue("B{$fila}", $datos->productor ?? '');
        $fila++;
        $sheet->setCellValue("A{$fila}", 'RUNSA:');
        $sheet->setCellValue("B{$fila}", $datos->runsa ?? '');
        $fila++;
        $sheet->setCellValue("A{$fila}", 'Municipio:');
        $sheet->setCellValue("B{$fila}", $datos->municipio ?? '');
        $fila++;
        $sheet->setCellValue("A{$fila}", 'Organización:');
        $sheet->setCellValue("B{$fila}", $datos->organizacion ?? '');
        $fila++;
        $sheet->setCellValue("A{$fila}", 'Gestión:');
        $sheet->setCellValue("B{$fila}", $gestion . ' (Julio ' . $gestion . ' - Junio ' . ($gestion + 1) . ')');
        $fila += 2;

        // ENCABEZADOS DE TABLA
        $sheet->setCellValue("A{$fila}", 'Mes');
        $sheet->setCellValue("B{$fila}", 'Cantidad (kg)');
        $sheet->setCellValue("C{$fila}", 'N° Entregas');
        $sheet->setCellValue("D{$fila}", 'Promedio/Entrega');
        $sheet->getStyle("A{$fila}:D{$fila}")->getFont()->setBold(true);
        $sheet->getStyle("A{$fila}:D{$fila}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4A90E2');
        $sheet->getStyle("A{$fila}:D{$fila}")->getFont()->getColor()->setARGB('FFFFFFFF');
        $fila++;

        // DATOS DE MESES
        $filaInicio = $fila;
        foreach ($datos->meses as $mes) {
            $promedio = $mes->num_entregas > 0 ? ($mes->cantidad_kg / $mes->num_entregas) : 0;
            
            $sheet->setCellValue("A{$fila}", $mes->mes_nombre . ' ' . ($mes->anio_mes ?? ''));
            $sheet->setCellValue("B{$fila}", (float)$mes->cantidad_kg);
            $sheet->setCellValue("C{$fila}", (int)$mes->num_entregas);
            $sheet->setCellValue("D{$fila}", $promedio);
            
            // Formato numérico
            $sheet->getStyle("B{$fila}")->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $sheet->getStyle("D{$fila}")->getNumberFormat()
                ->setFormatCode('#,##0.00');
            
            $fila++;
        }

        // FILA DE TOTALES
        $sheet->setCellValue("A{$fila}", 'TOTAL GESTIÓN ' . $gestion);
        $sheet->setCellValue("B{$fila}", (float)$datos->total_kg);
        $sheet->setCellValue("C{$fila}", (int)$datos->total_entregas);
        $sheet->setCellValue("D{$fila}", (float)$datos->promedio_entrega);
        
        $sheet->getStyle("A{$fila}:D{$fila}")->getFont()->setBold(true);
        $sheet->getStyle("A{$fila}:D{$fila}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle("B{$fila}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("D{$fila}")->getNumberFormat()->setFormatCode('#,##0.00');

        // AJUSTAR ANCHOS DE COLUMNA
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);

        // ALINEACIÓN
        $sheet->getStyle("B{$filaInicio}:D{$fila}")->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // GUARDAR ARCHIVO
        $filename = 'acopio_gestion_' . $productorId . '_' . $gestion . '.xlsx';
        $output = public_path('reportes/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(public_path('reportes'))) {
            mkdir(public_path('reportes'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($output);

        // Retornar archivo para descarga
        return response()->download($output)->deleteFileAfterSend(true);
    }
}
