<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\AcopioCosecha;
use Illuminate\Http\Request;

/**
 * Controlador para gestión de registros de aplicación de medicamentos
 * Asociados a cosechas de acopio
 */
class MedicamentoController extends Controller
{
    /**
     * Listar registros de medicamentos filtrados por cosecha
     * MODIFICACION 2025-11-18: Se agregaron relaciones anidadas para obtener datos del encabezado del formulario
     * Incluye: acopioCosecha -> apiario -> productor -> municipio -> provincia, departamento
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Medicamento::query();
        
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
        return Medicamento::findOrFail($id);
    }

    /**
     * Crear un nuevo registro de medicamento
     */
    public function store(Request $request)
    {
        $medicamento = Medicamento::create($request->all());
        return response()->json($medicamento, 201);
    }

    /**
     * Actualizar un registro existente
     */
    public function update(Request $request, $id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->update($request->all());
        return $medicamento;
    }

    /**
     * Eliminar un registro (soft delete)
     */
    public function destroy($id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Generar PDF del formulario de aplicación de medicamentos
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
        
        $medicamentos = Medicamento::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha')
            ->get();

        $html = $this->generarHtmlFormulario($cosecha, $medicamentos);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        // MODIFICACION 2025-11-18: Configurar orientacion horizontal para tabla ancha
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream("aplicacion_medicamentos_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de medicamentos
     * MODIFICACION 2025-11-18: Implementacion completa del reporte profesional
     * Incluye: encabezado con datos del apiario, tabla de registros, espacio para 2 firmas
     */
    private function generarHtmlFormulario($cosecha, $medicamentos)
    {
        // MODIFICACION 2025-11-18: Extraer datos del productor y ubicacion para el encabezado
        $productor = $cosecha->apiario->productor ?? null;
        $municipio = $productor->municipio ?? null;
        $apiario = $cosecha->apiario ?? null;
        
        // MODIFICACION 2025-11-18: Preparar datos del encabezado segun formulario fisico
        $nombreApiario = $apiario->nombre_apiario ?? $apiario->codigo_apiario ?? 'N/A';
        $registroSanitario = $productor->runsa ?? $productor->codigo_runsa ?? 'N/A';
        $nombreResponsable = ($productor->nombre ?? '') . ' ' . ($productor->apellidos ?? '');
        $nombreResponsable = trim($nombreResponsable) ?: ($productor->nombre_apellido ?? 'N/A');
        $ubicacion = $productor->direccion ?? 'N/A';
        $georeferenciacion = ($apiario->latitud ?? '') . ', ' . ($apiario->longitud ?? '');
        $georeferenciacion = trim($georeferenciacion, ', ') ?: 'N/A';
        $tipoManejo = $apiario->tipo_manejo ?? 'N/A';
        
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
                    font-size: 9px;
                    border: 1px solid #1565c0;
                }
                .tabla-registros td {
                    padding: 6px;
                    border: 1px solid #ddd;
                    font-size: 9px;
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
                Registro de aplicación de medicamentos
            </div>
            
            <!-- MODIFICACION 2025-11-18: Encabezado con datos del apiario y responsable -->
            <div class="encabezado">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <div class="label">NOMBRE DEL APIARIO:</div>
                            <div class="value">' . $nombreApiario . '</div>
                        </td>
                        <td style="width: 50%;">
                            <div class="label">REGISTRO SANITARIO:</div>
                            <div class="value">' . $registroSanitario . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">NOMBRE DEL RESPONSABLE:</div>
                            <div class="value">' . $nombreResponsable . '</div>
                        </td>
                        <td>
                            <div class="label">UBICACIÓN:</div>
                            <div class="value">' . $ubicacion . '</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">UBICACIÓN GEOREFERENCIAL:</div>
                            <div class="value">' . $georeferenciacion . '</div>
                        </td>
                        <td>
                            <div class="label">TIPO DE MANEJO:</div>
                            <div class="value">' . $tipoManejo . '</div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- MODIFICACION 2025-11-18: Tabla de registros de medicamentos -->
            <table class="tabla-registros">
                <thead>
                    <tr>
                        <th style="width: 5%;">N°</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 15%;">Nombre Producto</th>
                        <th style="width: 15%;">Principio Activo</th>
                        <th style="width: 10%;">Dosis Recomendada</th>
                        <th style="width: 10%;">Dosis Aplicada</th>
                        <th style="width: 15%;">Plagas Controladas</th>
                        <th style="width: 10%;">Período Espera</th>
                        <th style="width: 10%;">Nombre Encargado</th>
                    </tr>
                </thead>
                <tbody>';
        
        // MODIFICACION 2025-11-18: Iterar registros de medicamentos
        $contador = 1;
        foreach ($medicamentos as $medicamento) {
            $html .= '
                    <tr>
                        <td style="text-align: center;">' . $contador . '</td>
                        <td style="text-align: center;">' . ($medicamento->fecha ?? '') . '</td>
                        <td>' . ($medicamento->nombre_producto ?? '') . '</td>
                        <td>' . ($medicamento->principio_activo ?? '') . '</td>
                        <td>' . ($medicamento->dosis_recomendada ?? '') . '</td>
                        <td>' . ($medicamento->dosis_aplicada ?? '') . '</td>
                        <td>' . ($medicamento->plagas_controladas ?? '') . '</td>
                        <td>' . ($medicamento->periodo_espera_cosecha ?? '') . '</td>
                        <td>' . ($medicamento->nombre_encargado ?? '') . '</td>
                    </tr>';
            $contador++;
        }
        
        // MODIFICACION 2025-11-18: Mensaje si no hay registros
        if ($medicamentos->isEmpty()) {
            $html .= '
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #999;">
                            No hay registros de medicamentos para esta cosecha
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
