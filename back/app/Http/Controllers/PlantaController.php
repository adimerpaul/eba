<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use Illuminate\Http\Request;

class PlantaController extends Controller
{
    /**
     * Listado con búsqueda y filtros
     * GET /plantas?q=texto&municipio_id=1&per_page=20&page=1
     */
    public function index(Request $request)
    {
        $q = Planta::query()->with('municipio');

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('codigo_planta', 'like', "%{$term}%")
                    ->orWhere('nombre_planta', 'like', "%{$term}%")
                    ->orWhere('registro_sanitario', 'like', "%{$term}%")
                    ->orWhere('direccion', 'like', "%{$term}%");
            });
        }

        if ($request->filled('municipio_id')) {
            $q->where('municipio_id', (int) $request->get('municipio_id'));
        }

        $q->whereNull('deleted_at')->orderBy('nombre_planta');

        // Paginación opcional
        $perPage = (int) $request->get('per_page', 0);
        if ($perPage > 0) {
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function show(Planta $planta)
    {
        return $planta->load('municipio');
    }

    public function store(Request $request)
    {
        // Validación básica inline (sin FormRequest)
        $request->validate([
            'codigo_planta'     => ['required','string','max:10'],
            'nombre_planta'     => ['required','string','max:150'],
            'registro_sanitario'=> ['nullable','string','max:50'],
            'direccion'         => ['nullable','string'],
            'municipio_id'      => ['nullable','exists:municipios,id'],
        ]);

        $planta = Planta::create($request->all());
        return response()->json($planta->fresh('municipio'), 201);
    }

    public function update(Request $request, Planta $planta)
    {
        $request->validate([
            'codigo_planta'     => ['sometimes','required','string','max:10'],
            'nombre_planta'     => ['sometimes','required','string','max:150'],
            'registro_sanitario'=> ['nullable','string','max:50'],
            'direccion'         => ['nullable','string'],
            'municipio_id'      => ['nullable','exists:municipios,id'],
        ]);

        $planta->update($request->all());
        return $planta->fresh('municipio');
    }

    public function destroy(Planta $planta)
    {
        $planta->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }
}
