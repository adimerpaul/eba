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
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Plaga::query();
        
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
     */
    public function store(Request $request)
    {
        $plaga = Plaga::create($request->all());
        return response()->json($plaga, 201);
    }

    /**
     * Actualizar un registro existente
     */
    public function update(Request $request, $id)
    {
        $plaga = Plaga::findOrFail($id);
        $plaga->update($request->all());
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
     * Incluye todos los registros de la cosecha especificada
     */
    public function printFormulario(Request $request, $cosechaId)
    {
        $cosecha = AcopioCosecha::with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->findOrFail($cosechaId);
        
        $plagas = Plaga::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha')
            ->get();

        // Generar HTML del formulario con datos de cabecera y registros
        $html = $this->generarHtmlFormulario($cosecha, $plagas);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);

        return $pdf->stream("control_plagas_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de control de plagas
     * Se implementará con plantilla específica en paso posterior
     */
    private function generarHtmlFormulario($cosecha, $plagas)
    {
        $html = '<h1>Registro de control de plagas en colmenas</h1>';
        $html .= '<p>Cosecha: ' . $cosecha->id . '</p>';
        return $html;
    }
}
