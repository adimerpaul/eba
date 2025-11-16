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
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Limpieza::query();
        
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
     * Incluye todos los registros de la cosecha especificada
     */
    public function printFormulario(Request $request, $cosechaId)
    {
        $cosecha = AcopioCosecha::with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->findOrFail($cosechaId);
        
        $limpiezas = Limpieza::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha_aplicacion')
            ->get();

        $html = $this->generarHtmlFormulario($cosecha, $limpiezas);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);

        return $pdf->stream("limpieza_desinfeccion_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de limpieza
     * Se implementará con plantilla específica en paso posterior
     */
    private function generarHtmlFormulario($cosecha, $limpiezas)
    {
        $html = '<h1>Registro de limpieza y desinfección de equipos y herramienta apícolas</h1>';
        $html .= '<p>Cosecha: ' . $cosecha->id . '</p>';
        return $html;
    }
}
