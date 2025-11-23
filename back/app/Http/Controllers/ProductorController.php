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
        // MODIFICACIÓN 2025-11-23: Implementada búsqueda por tokens (palabras independientes)
        // para permitir búsquedas como "ZULMA TORREZ" que encuentre "ZULMA TERESA TORREZ CUENCA"
        if($request->productor_id){
            $q->where('id', $request->productor_id);
        }
        if ($search = trim((string) $request->get('search', ''))) {
            // 2025-11-23: Normalizar búsqueda eliminando espacios extras entre palabras
            $searchNormalized = preg_replace('/\s+/', ' ', $search);
            
            // 2025-11-23: Dividir búsqueda en tokens (palabras independientes)
            $tokens = array_filter(explode(' ', $searchNormalized));
            
            $q->where(function ($s) use ($search, $searchNormalized, $tokens) {
                // 2025-11-23: Búsqueda por tokens en nombre completo
                // Busca que TODAS las palabras estén presentes (sin importar orden)
                // Ejemplo: "Zulma Torrez" encuentra "ZULMA TERESA TORREZ CUENCA"
                if (count($tokens) > 1) {
                    // Búsqueda multi-token: cada palabra debe estar presente
                    $s->where(function($subQuery) use ($tokens) {
                        $concat = "CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellidos,''))";
                        foreach ($tokens as $token) {
                            $subQuery->whereRaw("{$concat} ILIKE ?", ["%{$token}%"]);
                        }
                    });
                } else {
                    // Búsqueda simple con una sola palabra
                    $s->whereRaw("CONCAT(COALESCE(nombre,''), ' ', COALESCE(apellidos,'')) ILIKE ?", ["%{$searchNormalized}%"]);
                }
                
                // 2025-11-23: Búsquedas directas en campos específicos
                // NOTA: RUNSA excluido intencionalmente (campo repetible que causa confusión)
                $s->orWhere('sub_codigo', 'ilike', "%{$search}%")
                  ->orWhere('numcarnet', 'ilike', "%{$search}%")
                  ->orWhere('comunidad', 'ilike', "%{$search}%")
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

            // VERIFICAR SI SE SOLICITA INCLUIR TRAZABILIDAD
            // Permite exportar con columnas mensuales cuando el modo esta activo en frontend
            $incluirTrazabilidad = $request->has('incluir_trazabilidad') && $request->input('incluir_trazabilidad') == true;
            
            if ($incluirTrazabilidad) {
                // EXPORTACION CON TRAZABILIDAD MENSUAL
                $gestion = (int) $request->input('gestion', date('Y'));
                $productoId = (int) $request->input('producto_id', 1);
                
                // Obtener acopios mensuales para todos los productores
                $productorIds = $todos->pluck('id')->toArray();
                $acopiosRequest = new Request([
                    'productor_ids' => $productorIds,
                    'gestion' => $gestion,
                    'producto_id' => $productoId
                ]);
                $acopiosResponse = $this->acopiosGestionLote($acopiosRequest);
                $acopiosData = json_decode($acopiosResponse->getContent(), true);
                
                // Mapear acopios por productor_id para acceso rapido
                $acopiosPorProductor = [];
                foreach ($acopiosData as $item) {
                    $acopiosPorProductor[$item['productor_id']] = $item;
                }
                
                // CORRECCION 2025-11-14: Ruta incorrecta - los templates estan en public/excel/ no en storage/app/excel/
                // Error detectado: File "...\eba\back\storage\app/excel/proveedores.xlsx" does not exist
                // $template = storage_path('app/excel/proveedores.xlsx'); // RUTA INCORRECTA
                $template = public_path('excel/proveedores.xlsx'); // RUTA CORRECTA
                $output = public_path('reportes/reporte_proveedor_trazabilidad.xlsx');

                $spreadsheet = IOFactory::load($template);
                $sheet = $spreadsheet->getActiveSheet();

                // COLUMNAS ORIGINALES + 13 COLUMNAS ADICIONALES (12 meses + total)
                $fila = 7;
                foreach ($todos as $u) {
                    $sheet->setCellValue("B{$fila}", $u->id);
                    $sheet->setCellValue("C{$fila}", $u->runsa);
                    $sheet->setCellValue("D{$fila}", $u->sub_codigo);
                    $sheet->setCellValue("E{$fila}", $u->municipio['nombre_municipio'] ?? '');
                    $sheet->setCellValue("F{$fila}", $u->nombre_completo);
                    $sheet->setCellValue("G{$fila}", $u->numcarnet);
                    $sheet->setCellValue("H{$fila}", $u->num_celular);
                    $sheet->setCellValue("I{$fila}", $u->comunidad ?? '');
                    $sheet->setCellValue("J{$fila}", $u->estado);
                    $sheet->setCellValue("K{$fila}", $u->fecha_registro);
                    
                    // AGREGAR COLUMNAS MENSUALES (L-W) Y TOTAL (X)
                    $acopios = $acopiosPorProductor[$u->id] ?? null;
                    if ($acopios) {
                        $meses = $acopios['meses_array'];
                        $columnas = ['L','M','N','O','P','Q','R','S','T','U','V','W'];
                        for ($i = 0; $i <= 11; $i++) {
                            $sheet->setCellValue("{$columnas[$i]}{$fila}", $meses[$i] ?? 0);
                        }
                        $sheet->setCellValue("X{$fila}", $acopios['total_kg']);
                    } else {
                        // Sin acopios, llenar con ceros
                        $columnas = ['L','M','N','O','P','Q','R','S','T','U','V','W'];
                        for ($i = 0; $i <= 11; $i++) {
                            $sheet->setCellValue("{$columnas[$i]}{$fila}", 0);
                        }
                        $sheet->setCellValue("X{$fila}", 0);
                    }
                    
                    $fila++;
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                return response()->download($output);
                
            } else {
                // EXPORTACION ORIGINAL SIN TRAZABILIDAD
                // Codigo mantenido para no afectar funcionalidad existente
                // CORRECCION 2025-11-14: Ruta incorrecta - los templates estan en public/excel/ no en storage/app/excel/
                // Error detectado: File "...\www\eba\back\storage\app/excel/proveedores.xlsx" does not exist
                // $template = storage_path('app/excel/proveedores.xlsx'); // RUTA INCORRECTA
                $template = public_path('excel/proveedores.xlsx'); // RUTA CORRECTA
                $output = public_path('reportes/reporte_proveedor.xlsx');

                $spreadsheet = IOFactory::load($template);
                $sheet = $spreadsheet->getActiveSheet();

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

                $writer = new Xlsx($spreadsheet);
                $writer->save($output);

                return response()->download($output);
            }
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
        
        // 2025-11-23: Asegurar que runsa nunca sea NULL (campo NOT NULL en DB)
        // Si no viene en el request o es null, establecer como string vacío
        if (!isset($data['runsa']) || $data['runsa'] === null) {
            $data['runsa'] = '';
        }

        $p = Productor::create($data);
        return response()->json($p->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza']), 201);
    }

    public function show(Productor $productor)
    {
        return $productor->load([
            'municipio:id,nombre_municipio,provincia_id,departamento_id',
            'organizacion',
            'certificaciones',
            // Agregar runsas
            'runsas',
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
        // 2025-11-23: Validar que el productor no tenga relaciones criticas antes de eliminar
        $tieneApiarios = $productor->apiarios()->exists();
        
        if ($tieneApiarios) {
            return response()->json([
                'message' => 'No se puede eliminar el productor porque tiene apiarios registrados'
            ], 422);
        }
        
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

    /**
     * Obtiene acopios mensuales de multiples productores en lote
     * Optimizado para carga masiva en tabla principal
     * Permite visualizar trazabilidad de entregas por temporada
     * 
     * POST /api/productores/acopios-gestion-lote
     * Body: { productor_ids: [1,2,3,...], gestion: 2025, producto_id: 1 }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function acopiosGestionLote(Request $request)
    {
        // Validar parametros de entrada
        $request->validate([
            'productor_ids' => 'required|array|min:1|max:200',
            'productor_ids.*' => 'integer|exists:productores,id',
            'gestion' => 'required|integer|min:2020|max:2030',
            'producto_id' => 'nullable|integer|exists:productos,id'
        ]);

        $productorIds = $request->input('productor_ids');
        $gestion = (int) $request->input('gestion');
        $productoId = $request->input('producto_id', 1);

        // Calcular rango de fechas para la gestion apicola (julio a junio)
        $fechaInicio = "{$gestion}-07-01";
        $fechaFin = ($gestion + 1) . "-06-30";

        // Query SQL optimizado para obtener acopios de multiples productores
        // Agrupa por productor y mes (offset 0-11 donde 0=Julio, 11=Junio)
        $resultados = DB::select("
            WITH productores_input AS (
                SELECT unnest(ARRAY[" . implode(',', array_map('intval', $productorIds)) . "]) AS productor_id
            ),
            apiarios_productores AS (
                SELECT 
                    p.productor_id,
                    array_agg(a.id) FILTER (WHERE a.id IS NOT NULL) AS apiario_ids
                FROM productores_input p
                LEFT JOIN apiarios a ON a.productor_id = p.productor_id AND a.deleted_at IS NULL
                GROUP BY p.productor_id
            ),
            acopios_mensuales AS (
                SELECT 
                    ap.productor_id,
                    CASE 
                        WHEN EXTRACT(MONTH FROM ac.fecha_cosecha) >= 7 
                        THEN EXTRACT(MONTH FROM ac.fecha_cosecha) - 7
                        ELSE EXTRACT(MONTH FROM ac.fecha_cosecha) + 5
                    END AS offset_mes,
                    SUM(ac.cantidad_kg) AS cantidad_kg
                FROM apiarios_productores ap
                LEFT JOIN acopio_cosechas ac ON ac.apiario_id = ANY(ap.apiario_ids)
                    AND ac.producto_id = :producto_id
                    AND ac.fecha_cosecha >= :fecha_inicio::date
                    AND ac.fecha_cosecha <= :fecha_fin::date
                    AND ac.deleted_at IS NULL
                WHERE ap.apiario_ids IS NOT NULL
                GROUP BY ap.productor_id, offset_mes
            )
            SELECT 
                pi.productor_id,
                COALESCE(
                    jsonb_object_agg(
                        am.offset_mes::text, 
                        COALESCE(am.cantidad_kg, 0)
                    ) FILTER (WHERE am.offset_mes IS NOT NULL),
                    '{}'::jsonb
                ) AS meses_json,
                COALESCE(SUM(am.cantidad_kg), 0)::numeric(10,2) AS total_kg
            FROM productores_input pi
            LEFT JOIN acopios_mensuales am ON am.productor_id = pi.productor_id
            GROUP BY pi.productor_id
        ", [
            'producto_id' => $productoId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);

        // Transformar resultados a formato array con indices 0-11 para facilitar acceso desde frontend
        $response = array_map(function($row) {
            $mesesJson = json_decode($row->meses_json, true);
            
            // Convertir a array con indices 0-11 garantizando todos los meses
            $mesesArray = [];
            for ($i = 0; $i <= 11; $i++) {
                $mesesArray[$i] = isset($mesesJson[(string)$i]) 
                    ? (float) $mesesJson[(string)$i] 
                    : 0.0;
            }
            
            return [
                'productor_id' => (int) $row->productor_id,
                'meses_array' => $mesesArray,
                'total_kg' => (float) $row->total_kg
            ];
        }, $resultados);

        return response()->json($response);
    }

    /**
     * Verificar vencimientos proximos (30 dias) para alertas
     * Creado: 2025-11-21
     * @param int $id ID del productor
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarVencimientos($id)
    {
        $productor = Productor::findOrFail($id);
        
        // Fecha limite: hoy + 30 dias
        $fechaHoy = now();
        $fechaLimite = now()->addDays(30);
        
        // Contar certificaciones por vencer (estado VIGENTE y fecha_vencimiento entre hoy y +30 dias)
        $certificacionesPorVencer = DB::table('productor_certificaciones')
            ->where('productor_id', $id)
            ->where('estado', 'VIGENTE')
            ->whereBetween('fecha_vencimiento', [$fechaHoy, $fechaLimite])
            ->count();
        
        // Contar RUNSAs por vencer (estado VIGENTE y fecha_vencimiento entre hoy y +30 dias)
        $runsasPorVencer = DB::table('productor_runsas')
            ->where('productor_id', $id)
            ->where('estado', 'VIGENTE')
            ->whereBetween('fecha_vencimiento', [$fechaHoy, $fechaLimite])
            ->count();
        
        return response()->json([
            'certificaciones_por_vencer' => $certificacionesPorVencer,
            'runsas_por_vencer' => $runsasPorVencer
        ]);
    }

    /**
     * Verificar si existe un productor duplicado por CI o RUNSA
     * Creado: 2025-11-21
     * @param Request $request Parametros: numcarnet (opcional), runsa (opcional)
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarDuplicado(Request $request)
    {
        $numcarnet = $request->query('numcarnet');
        $runsa = $request->query('runsa');
        
        // Si no se proporciona ningun parametro, retornar que no existe duplicado
        if (empty($numcarnet) && empty($runsa)) {
            return response()->json([
                'existe' => false,
                'productor_id' => null,
                'datos' => null
            ]);
        }
        
        // Buscar productor por CI o RUNSA
        $query = Productor::query();
        
        if (!empty($numcarnet) && !empty($runsa)) {
            // Buscar por CI O RUNSA
            $query->where(function($q) use ($numcarnet, $runsa) {
                $q->where('numcarnet', $numcarnet)
                  ->orWhere('runsa', $runsa);
            });
        } elseif (!empty($numcarnet)) {
            // Solo buscar por CI
            $query->where('numcarnet', $numcarnet);
        } else {
            // Solo buscar por RUNSA
            $query->where('runsa', $runsa);
        }
        
        $productor = $query->first();
        
        if ($productor) {
            return response()->json([
                'existe' => true,
                'productor_id' => $productor->id,
                'datos' => [
                    'nombre_completo' => $productor->nombre_completo,
                    'numcarnet' => $productor->numcarnet,
                    'runsa' => $productor->runsa,
                    'estado' => $productor->estado
                ]
            ]);
        }
        
        return response()->json([
            'existe' => false,
            'productor_id' => null,
            'datos' => null
        ]);
    }

    /**
     * Generar reporte individual de productor en PDF
     * 2025-11-23: Creado para imprimir registro desde tabla de productores
     * @param Productor $productor
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generarReporteIndividual(Productor $productor)
    {
        $productor->load([
            'municipio:id,nombre_municipio,provincia_id,departamento_id',
            'municipio.provincia:id,nombre_provincia',
            'municipio.departamento:id,nombre_departamento',
            'organizacion',
            'certificaciones',
            'runsas',
            'apiarios.colmenas'
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'REGISTRO DE PRODUCTOR');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $fila = 3;
        
        $sheet->setCellValue("A{$fila}", 'DATOS PERSONALES');
        $sheet->mergeCells("A{$fila}:D{$fila}");
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$fila}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $fila++;

        $datosPersonales = [
            ['ID:', $productor->id],
            ['Nombre completo:', $productor->nombre_completo],
            ['CI:', $productor->numcarnet],
            ['Expedido:', $productor->expedido ?? ''],
            ['RUNSA:', $productor->runsa ?? ''],
            ['Sub Codigo:', $productor->sub_codigo ?? ''],
            ['Celular:', $productor->num_celular ?? ''],
            ['Fecha nacimiento:', $productor->fec_nacimiento ?? ''],
            ['Sexo:', $productor->sexo == 1 ? 'Masculino' : ($productor->sexo == 2 ? 'Femenino' : '')],
            ['Direccion:', $productor->direccion ?? ''],
            ['Comunidad:', $productor->comunidad ?? ''],
            ['Estado:', $productor->estado],
            ['Fecha registro:', $productor->fecha_registro],
        ];

        foreach ($datosPersonales as $dato) {
            $sheet->setCellValue("A{$fila}", $dato[0]);
            $sheet->setCellValue("B{$fila}", $dato[1]);
            $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
            $fila++;
        }

        $fila++;
        $sheet->setCellValue("A{$fila}", 'UBICACION');
        $sheet->mergeCells("A{$fila}:D{$fila}");
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$fila}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $fila++;

        $ubicacion = [
            ['Departamento:', $productor->municipio->departamento->nombre_departamento ?? ''],
            ['Provincia:', $productor->municipio->provincia->nombre_provincia ?? ''],
            ['Municipio:', $productor->municipio->nombre_municipio ?? ''],
            ['Organizacion:', $productor->organizacion->nombre_organiza ?? ''],
        ];

        foreach ($ubicacion as $dato) {
            $sheet->setCellValue("A{$fila}", $dato[0]);
            $sheet->setCellValue("B{$fila}", $dato[1]);
            $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
            $fila++;
        }

        $fila++;
        $sheet->setCellValue("A{$fila}", 'APIARIOS Y COLMENAS');
        $sheet->mergeCells("A{$fila}:D{$fila}");
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$fila}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $fila++;

        if ($productor->apiarios->count() > 0) {
            foreach ($productor->apiarios as $apiario) {
                $sheet->setCellValue("A{$fila}", 'Apiario:');
                $sheet->setCellValue("B{$fila}", $apiario->nombre ?? 'Sin nombre');
                $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
                $fila++;
                $sheet->setCellValue("A{$fila}", 'Ubicacion:');
                $sheet->setCellValue("B{$fila}", $apiario->ubicacion ?? '');
                $fila++;
                $sheet->setCellValue("A{$fila}", 'Colmenas:');
                $sheet->setCellValue("B{$fila}", $apiario->colmenas->count());
                $fila++;
                $fila++;
            }
        } else {
            $sheet->setCellValue("A{$fila}", 'Sin apiarios registrados');
            $fila++;
        }

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(40);

        $filename = 'productor_' . $productor->id . '_' . str_replace(' ', '_', $productor->nombre_completo) . '.xlsx';
        $output = public_path('reportes/' . $filename);

        if (!file_exists(public_path('reportes'))) {
            mkdir(public_path('reportes'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($output);

        return response()->download($output)->deleteFileAfterSend(true);
    }
}
