<?php

namespace App\Http\Controllers;

use App\Models\Plaga;
use App\Models\AcopioCosecha;
use Illuminate\Http\Request;

/**
 * Controlador para gestión de registros de control de plagas en colmenas
 * Asociados a cosechas de acopio
 */
class PlagaController extends Controller
{
    /**
     * Listar registros de plagas filtrados por cosecha
     * MODIFICACION 2025-11-18: Se agregaron relaciones anidadas para obtener datos del encabezado del formulario
     * Incluye: acopioCosecha -> apiario -> productor -> municipio -> provincia, departamento
     * Esto permite mostrar: Registro Sanitario, Dpto, Nombre Apicultor, Nombre Apiario, Provincia, Municipio, etc.
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Plaga::query();
        
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
        return Plaga::findOrFail($id);
    }

    /**
     * Crear un nuevo registro de plaga
     * MODIFICACION 2025-11-18: Validar que nombre_plaga tenga valor (no nullable en BD)
     * Este campo almacena el nombre/codigo del apiario
     */
    public function store(Request $request)
    {
        // MODIFICACION 2025-11-18: Asegurar que nombre_plaga tenga un valor valido
        // Si viene vacio, usar un valor por defecto para evitar error de BD
        $data = $request->all();
        if (empty($data['nombre_plaga'])) {
            $data['nombre_plaga'] = 'Sin nombre';
        }
        
        $plaga = Plaga::create($data);
        return response()->json($plaga, 201);
    }

    /**
     * Actualizar un registro existente
     * MODIFICACION 2025-11-18: Validar que nombre_plaga tenga valor (no nullable en BD)
     */
    public function update(Request $request, $id)
    {
        $plaga = Plaga::findOrFail($id);
        
        // MODIFICACION 2025-11-18: Asegurar que nombre_plaga tenga un valor valido
        $data = $request->all();
        if (empty($data['nombre_plaga'])) {
            $data['nombre_plaga'] = 'Sin nombre';
        }
        
        $plaga->update($data);
        return $plaga;
    }

    /**
     * Eliminar un registro (soft delete)
     */
    public function destroy($id)
    {
        $plaga = Plaga::findOrFail($id);
        $plaga->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Generar PDF del formulario de control de plagas
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
        
        $plagas = Plaga::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha')
            ->get();

        // Generar HTML del formulario con datos de cabecera y registros
        $html = $this->generarHtmlFormulario($cosecha, $plagas);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        // MODIFICACION 2025-11-18: Configurar orientacion horizontal para tabla ancha
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream("control_plagas_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de control de plagas
     * MODIFICACION 2025-11-18: Implementacion completa del reporte profesional
     * Incluye: encabezado con datos del productor, tabla de registros, espacio para 2 firmas
     */
    private function generarHtmlFormulario($cosecha, $plagas)
    {
        // MODIFICACION 2025-11-18: Extraer datos del productor y ubicacion para el encabezado
        $productor = $cosecha->apiario->productor ?? null;
        $municipio = $productor->municipio ?? null;
        $provincia = $municipio->provincia ?? null;
        $departamento = $municipio->departamento ?? null;
        $apiario = $cosecha->apiario ?? null;
        
        // MODIFICACION 2025-11-18: Preparar datos del encabezado segun formulario fisico
        $registroSanitario = $productor->runsa ?? $productor->codigo_runsa ?? 'N/A';
        $dpto = $departamento->nombre_departamento ?? 'N/A';
        $nombreApicultor = ($productor->nombre ?? '') . ' ' . ($productor->apellidos ?? '');
        $nombreApicultor = trim($nombreApicultor) ?: ($productor->nombre_apellido ?? 'N/A');
        $nombreApiario = $apiario->nombre_apiario ?? $apiario->codigo_apiario ?? 'N/A';
        $provinciaNombre = $provincia->nombre_provincia ?? 'N/A';
        $municipioNombre = $municipio->nombre_municipio ?? 'N/A';
        $georeferenciacion = ($apiario->latitud ?? '') . ', ' . ($apiario->longitud ?? '');
        $georeferenciacion = trim($georeferenciacion, ', ') ?: 'N/A';
        $localidad = $productor->direccion ?? 'N/A';
        
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
                Registro de control de plagas en colmenas
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
                            <div class="label">DEPARTAMENTO:</div>
                            <div class="value">' . $dpto . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">NOMBRE DEL APICULTOR O RESPONSABLE:</div>
                            <div class="value">' . $nombreApicultor . '</div>
                        </td>
                        <td>
                            <div class="label">PROVINCIA:</div>
                            <div class="value">' . $provinciaNombre . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">NOMBRE DEL APIARIO:</div>
                            <div class="value">' . $nombreApiario . '</div>
                        </td>
                        <td>
                            <div class="label">MUNICIPIO:</div>
                            <div class="value">' . $municipioNombre . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">GEOREFERENCIACIÓN:</div>
                            <div class="value">' . $georeferenciacion . '</div>
                        </td>
                        <td>
                            <div class="label">LOCALIDAD:</div>
                            <div class="value">' . $localidad . '</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- MODIFICACION 2025-11-18: Tabla de registros de control de plagas -->
            <table class="tabla-registros">
                <thead>
                    <tr>
                        <th style="width: 5%;">N°</th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 18%;">Nombre o Código del Apiario</th>
                        <th style="width: 18%;">Plaga Presente en el Apiario</th>
                        <th style="width: 18%;">Daño Visible en el Apiario</th>
                        <th style="width: 18%;">Método de Control Utilizado</th>
                        <th style="width: 11%;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>';
        
        // MODIFICACION 2025-11-18: Iterar registros de plagas
        $contador = 1;
        foreach ($plagas as $plaga) {
            $html .= '
                    <tr>
                        <td style="text-align: center;">' . $contador . '</td>
                        <td style="text-align: center;">' . ($plaga->fecha ?? '') . '</td>
                        <td>' . ($plaga->nombre_plaga ?? '') . '</td>
                        <td>' . ($plaga->plaga_presente ?? '') . '</td>
                        <td>' . ($plaga->daño_visible_apiario ?? '') . '</td>
                        <td>' . ($plaga->medidas_control_celdilla ?? '') . '</td>
                        <td>' . ($plaga->observaciones ?? '') . '</td>
                    </tr>';
            $contador++;
        }
        
        // MODIFICACION 2025-11-18: Mensaje si no hay registros
        if ($plagas->isEmpty()) {
            $html .= '
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                            No hay registros de control de plagas para esta cosecha
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
                                ' . $nombreApicultor . '
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
