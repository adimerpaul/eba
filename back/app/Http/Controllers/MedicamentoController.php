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
     */
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $query = Medicamento::query();
        
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
     * Incluye todos los registros de la cosecha especificada
     */
    public function printFormulario(Request $request, $cosechaId)
    {
        $cosecha = AcopioCosecha::with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->findOrFail($cosechaId);
        
        $medicamentos = Medicamento::where('acopio_cosecha_id', $cosechaId)
            ->orderBy('fecha')
            ->get();

        $html = $this->generarHtmlFormulario($cosecha, $medicamentos);

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($html);

        return $pdf->stream("aplicacion_medicamentos_{$cosechaId}.pdf");
    }

    /**
     * Generar HTML del formulario de medicamentos
     * Se implementará con plantilla específica en paso posterior
     */
    private function generarHtmlFormulario($cosecha, $medicamentos)
    {
        $html = '<h1>Registro de aplicación de medicamentos</h1>';
        $html .= '<p>Cosecha: ' . $cosecha->id . '</p>';
        return $html;
    }
}
