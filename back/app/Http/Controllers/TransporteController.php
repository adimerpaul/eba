<?php

namespace App\Http\Controllers;

use App\Models\Transporte;
use Illuminate\Http\Request;

class TransporteController extends Controller
{
    /**
     * Listado con búsqueda y (opcional) paginación
     * GET /transportes?q=texto&per_page=20&page=1
     */
    public function index(Request $request)
    {
        $q = Transporte::query()->where('id', '>', 0);

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('empresa', 'like', "%{$term}%")
                    ->orWhere('placa', 'like', "%{$term}%")
                    ->orWhere('responsable', 'like', "%{$term}%");
            });
        }

        // Solo activos (no soft-deleted)
        $q->whereNull('deleted_at')->orderBy('empresa');

        // Paginación opcional
        $perPage = (int) $request->get('per_page', 0);
        if ($perPage > 0) {
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function show(Transporte $transporte)
    {
        return $transporte;
    }

    public function store(Request $request)
    {
        $transporte = Transporte::create($request->all());
        return response()->json($transporte, 201);
    }

    public function update(Request $request, Transporte $transporte)
    {
        $transporte->update($request->all());
        return $transporte;
    }

    public function destroy(Transporte $transporte)
    {
        $transporte->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }

    /**
     * Obtener estadísticas completas de un transporte
     * GET /transportes/{id}/estadisticas-completas
     */
    public function estadisticasCompletas(Transporte $transporte)
    {
        return response()->json([
            'transporte' => [
                'id' => $transporte->id,
                'empresa' => $transporte->empresa,
                'placa' => $transporte->placa,
                'responsable' => $transporte->responsable,
            ],
            'estadisticas' => $transporte->estadisticas_completas
        ]);
    }
}
