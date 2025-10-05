<?php

// app/Http/Controllers/DepartamentoController.php
namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartamentoController extends Controller
{
    public function index(Request $request)
    {
        $q = Departamento::query();

        if ($search = trim($request->get('search',''))) {
            $q->where('nombre_departamento', 'ilike', "%{$search}%");
        }

        $q->orderBy('nombre_departamento','asc');

        if ($request->boolean('paginate', false)) {
            $perPage = max(5, min((int)$request->get('per_page', 20), 100));
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_departamento' => ['required','string','max:120', Rule::unique('departamentos','nombre_departamento')],
        ]);

        $dep = Departamento::create($data);
        return response()->json($dep, 201);
    }

    public function show(Departamento $departamento)
    {
        return $departamento->loadCount(['provincias','municipios']);
    }

    public function update(Request $request, Departamento $departamento)
    {
        $data = $request->validate([
            'nombre_departamento' => [
                'required','string','max:120',
                Rule::unique('departamentos','nombre_departamento')->ignore($departamento->id)
            ],
        ]);

        $departamento->update($data);
        return $departamento;
    }

    public function destroy(Departamento $departamento)
    {
        if ($departamento->provincias()->exists() || $departamento->municipios()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: tiene provincias o municipios asociados.'
            ], 409);
        }

        $departamento->delete();
        return response()->json(['message'=>'Eliminado'], 200);
    }
}
