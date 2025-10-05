<?php

// app/Http/Controllers/ProvinciaController.php
namespace App\Http\Controllers;

use App\Models\Provincia;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProvinciaController extends Controller
{
    public function index(Request $request)
    {
        $q = Provincia::with('departamento:id,nombre_departamento');

        if ($search = trim($request->get('search',''))) {
            $q->where('nombre_provincia', 'ilike', "%{$search}%");
        }
        if ($depId = $request->get('departamento_id')) {
            $q->where('departamento_id', $depId);
        }

        $q->orderBy('nombre_provincia','asc');

        if ($request->boolean('paginate', false)) {
            $perPage = max(5, min((int)$request->get('per_page', 20), 100));
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_provincia' => ['required','string','max:150'],
            'departamento_id'  => ['required','integer','exists:departamentos,id'],
        ]);

        // Unicidad por departamento
        $exists = Provincia::where('departamento_id', $data['departamento_id'])
            ->whereRaw('LOWER(nombre_provincia) = ?', [mb_strtolower($data['nombre_provincia'])])
            ->exists();

        if ($exists) {
            return response()->json(['message'=>'Ya existe una provincia con ese nombre en el departamento seleccionado.'], 422);
        }

        $prov = Provincia::create($data);
        return response()->json($prov->load('departamento:id,nombre_departamento'), 201);
    }

    public function show(Provincia $provincia)
    {
        return $provincia->load(['departamento:id,nombre_departamento'])->loadCount('municipios');
    }

    public function update(Request $request, Provincia $provincia)
    {
        $data = $request->validate([
            'nombre_provincia' => ['required','string','max:150'],
            'departamento_id'  => ['required','integer','exists:departamentos,id'],
        ]);

        $exists = Provincia::where('departamento_id', $data['departamento_id'])
            ->whereRaw('LOWER(nombre_provincia) = ?', [mb_strtolower($data['nombre_provincia'])])
            ->where('id','!=',$provincia->id)
            ->exists();

        if ($exists) {
            return response()->json(['message'=>'Ya existe una provincia con ese nombre en el departamento seleccionado.'], 422);
        }

        $provincia->update($data);
        return $provincia->load('departamento:id,nombre_departamento');
    }

    public function destroy(Provincia $provincia)
    {
        if ($provincia->municipios()->exists()) {
            return response()->json(['message'=>'No se puede eliminar: tiene municipios asociados.'], 409);
        }

        $provincia->delete();
        return response()->json(['message'=>'Eliminado'], 200);
    }
}
