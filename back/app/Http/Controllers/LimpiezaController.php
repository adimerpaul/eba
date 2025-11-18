<?php

namespace App\Http\Controllers;

use App\Models\Limpieza;
use App\Models\AcopioCosecha;
use Illuminate\Http\Request;

/**
 * Controlador para gestión de registros de limpieza y desinfección
 * Asociados a cosechas de acopio
 */
class LimpiezaController extends Controller
{
    /**
     * Listar registros de limpiezas filtrados por cosecha
     * MODIFICACION 2025-11-18: Se agregaron relaciones anidadas para obtener datos del encabezado del formulario
     * Incluye: acopioCosecha -> apiario -> productor -> municipio -> provincia, departamento
     * Esto permite mostrar: Registro Sanitario, Dpto, Nombre Responsable, Provincia, Ubicacion UPA, etc.
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Limpieza::query();
        
        // MODIFICACION 2025-11-18: Agregar eager loading de relaciones para datos de encabezado
        $query->with([
            'acopioCosecha.apiario.productor.municipio.provincia',
            'acopioCosecha.apiario.productor.municipio.departamento',
            'acopioCosecha.apiario'
        ]);
        
        if ($cosechaId) {
            $query->where('acopio_cosecha_id', $cosechaId);
        }
        
        return $query->orderByDesc('id')->get();
    }

    /**
     * Obtener un registro específico
     */
    public function show($id)
    {
        return Limpieza::findOrFail($id);
    }

    /**
     * Crear un nuevo registro de limpieza
     */
    public function store(Request $request)
    {
        $limpieza = Limpieza::create($request->all());
        return response()->json($limpieza, 201);
    }

    /**
     * Actualizar un registro existente
     */
    public function update(Request $request, $id)
    {
        $limpieza = Limpieza::findOrFail($id);
        $limpieza->update($request->all());
        return $limpieza;
    }

    /**
     * Eliminar un registro (soft delete)
     */
    public function destroy($id)
    {
        $limpieza = Limpieza::findOrFail($id);
        $limpieza->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Generar PDF del formulario de limpieza y desinfección
     * MODIFICACION 2025-11-18: Se añadieron relaciones completas para datos del encabezado
     * Incluye todos los registros de la cosecha especificada con formato profesional
     */
    public function printFormulario(Request $request, $cosechaId)
    {
        // MODIFICACION 2025-11-18: Cargar relaciones anidadas para encabezado completo
        $cosecha = AcopioCosecha::with([
            'apiario.productor.municipio.provincia',
            'apiario.productor.municipio.departamento',
            'apiario',
            'producto'
        ])->findOrFail($cosechaId);
        
        $limpiezas = Limpieza::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha_aplicacion')
            ->get();

        $html = $this->generarHtmlFormulario($cosecha, $limpiezas);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        // MODIFICACION 2025-11-18: Configurar orientacion horizontal para tabla ancha
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream("limpieza_desinfeccion_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de limpieza
     * MODIFICACION 2025-11-18: Implementacion completa del reporte profesional
     * Incluye: encabezado con datos del productor, tabla de registros, espacio para firma
     */
    private function generarHtmlFormulario($cosecha, $limpiezas)
    {
        // MODIFICACION 2025-11-18: Extraer datos del productor y ubicacion para el encabezado
        $productor = $cosecha->apiario->productor ?? null;
        $municipio = $productor->municipio ?? null;
        $provincia = $municipio->provincia ?? null;
        $departamento = $municipio->departamento ?? null;
        $apiario = $cosecha->apiario ?? null;
        
        // MODIFICACION 2025-11-18: Preparar datos del encabezado
        $registroSanitario = $productor->runsa ?? $productor->codigo_runsa ?? 'N/A';
        $dpto = $departamento->nombre_departamento ?? 'N/A';
        $nombreResponsable = ($productor->nombre ?? '') . ' ' . ($productor->apellidos ?? '');
        $nombreResponsable = trim($nombreResponsable) ?: ($productor->nombre_apellido ?? 'N/A');
        $provinciaNombre = $provincia->nombre_provincia ?? 'N/A';
        $ubicacionUpa = $apiario->lugar_apiario ?? 'N/A';
        $municipioNombre = $municipio->nombre_municipio ?? 'N/A';
        $telefono = $productor->num_celular ?? $productor->celular ?? 'N/A';
        $localidad = $productor->direccion ?? 'N/A';
        $comunidad = $productor->comunidad ?? 'N/A';
        
        // MODIFICACION 2025-11-18: Construir HTML con estructura profesional
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                /* MODIFICACION 2025-11-18: Estilos para reporte profesional */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 11px;
                    margin: 20px;
                    color: #333;
                }
                .titulo {
                    text-align: center;
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    text-transform: uppercase;
                }
                .encabezado {
                    background-color: #f5f5f5;
                    padding: 10px;
                    margin-bottom: 15px;
                    border: 1px solid #ddd;
                }
                .encabezado table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .encabezado td {
                    padding: 5px;
                    vertical-align: top;
                }
                .encabezado .label {
                    font-weight: bold;
                    font-size: 9px;
                    color: #555;
                }
                .encabezado .value {
                    font-size: 11px;
                    color: #000;
                }
                .tabla-registros {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 30px;
                }
                .tabla-registros th {
                    background-color: #1976d2;
                    color: white;
                    padding: 8px;
                    text-align: left;
                    font-size: 10px;
                    border: 1px solid #1565c0;
                }
                .tabla-registros td {
                    padding: 6px;
                    border: 1px solid #ddd;
                    font-size: 10px;
                }
                .tabla-registros tbody tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .seccion-firma {
                    margin-top: 40px;
                    text-align: center;
                }
                .linea-firma {
                    border-top: 1px solid #000;
                    width: 300px;
                    margin: 60px auto 5px auto;
                }
                .texto-firma {
                    font-size: 11px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <!-- MODIFICACION 2025-11-18: Titulo del formulario -->
            <div class="titulo">
                Registro de limpieza y desinfección de equipos y herramienta apícolas
            </div>
            
            <!-- MODIFICACION 2025-11-18: Encabezado con datos del productor y ubicacion -->
            <div class="encabezado">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <div class="label">REGISTRO SANITARIO:</div>
                            <div class="value">' . $registroSanitario . '</div>
                        </td>
                        <td style="width: 50%;">
                            <div class="label">DPTO.:</div>
                            <div class="value">' . $dpto . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">NOMBRE DEL RESPONSABLE:</div>
                            <div class="value">' . $nombreResponsable . '</div>
                        </td>
                        <td>
                            <div class="label">PROVINCIA:</div>
                            <div class="value">' . $provinciaNombre . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">UBICACIÓN DE LA UPA:</div>
                            <div class="value">' . $ubicacionUpa . '</div>
                        </td>
                        <td>
                            <div class="label">MUNICIPIO:</div>
                            <div class="value">' . $municipioNombre . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">TELÉFONO:</div>
                            <div class="value">' . $telefono . '</div>
                        </td>
                        <td>
                            <div class="label">LOCALIDAD:</div>
                            <div class="value">' . $localidad . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="label">COMUNIDAD:</div>
                            <div class="value">' . $comunidad . '</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- MODIFICACION 2025-11-18: Tabla de registros de limpieza -->
            <table class="tabla-registros">
                <thead>
                    <tr>
                        <th style="width: 5%;">N°</th>
                        <th style="width: 22%;">Equipo/Herramienta</th>
                        <th style="width: 20%;">Material Recubrimiento</th>
                        <th style="width: 20%;">Método Limpieza</th>
                        <th style="width: 20%;">Producto Químico</th>
                        <th style="width: 13%;">Fecha Aplicación</th>
                    </tr>
                </thead>
                <tbody>';
        
        // MODIFICACION 2025-11-18: Iterar registros de limpieza
        $contador = 1;
        foreach ($limpiezas as $limpieza) {
            $html .= '
                    <tr>
                        <td style="text-align: center;">' . $contador . '</td>
                        <td>' . ($limpieza->equipo_herramienta_material ?? '') . '</td>
                        <td>' . ($limpieza->material_recubrimiento ?? '') . '</td>
                        <td>' . ($limpieza->metodo_limpieza_utilizado ?? '') . '</td>
                        <td>' . ($limpieza->producto_quimico_desinfeccion ?? '') . '</td>
                        <td style="text-align: center;">' . ($limpieza->fecha_aplicacion ?? '') . '</td>
                    </tr>';
            $contador++;
        }
        
        // MODIFICACION 2025-11-18: Mensaje si no hay registros
        if ($limpiezas->isEmpty()) {
            $html .= '
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                            No hay registros de limpieza para esta cosecha
                        </td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <!-- MODIFICACION 2025-11-18: Seccion de firmas (Productor y Responsable Tecnico) -->
            <div class="seccion-firma">
                <table style="width: 100%; margin-top: 40px;">
                    <tr>
                        <td style="width: 50%; text-align: center;">
                            <div class="linea-firma" style="margin: 60px auto 5px auto;"></div>
                            <div class="texto-firma">Firma del Productor</div>
                            <div style="font-size: 9px; color: #666; margin-top: 5px;">
                                ' . $nombreResponsable . '
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center;">
                            <div class="linea-firma" style="margin: 60px auto 5px auto;"></div>
                            <div class="texto-firma">Firma del Responsable Técnico</div>
                            <div style="font-size: 9px; color: #666; margin-top: 5px;">
                                SENASAG
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
