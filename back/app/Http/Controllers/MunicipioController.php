<?php

// app/Http/Controllers/MunicipioController.php
namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Departamento;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function index(Request $request)
    {
        $q = Municipio::with([
            'departamento:id,nombre_departamento',
            'provincia:id,nombre_provincia,departamento_id'
        ]);

        if ($search = trim($request->get('search',''))) {
            $q->where(function($s) use ($search){
                $s->where('nombre_municipio','ilike',"%{$search}%")
                    ->orWhere('zona','ilike',"%{$search}%")
                    ->orWhere('region','ilike',"%{$search}%");
            });
        }
        if ($depId = $request->get('departamento_id')) {
            $q->where('departamento_id', $depId);
        }
        if ($provId = $request->get('provincia_id')) {
            $q->where('provincia_id', $provId);
        }

        $q->orderBy('nombre_municipio','asc');

        if ($request->boolean('paginate', false)) {
            $perPage = max(5, min((int)$request->get('per_page', 20), 100));
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_municipio' => ['required','string','max:160'],
            'provincia_id'     => ['required','integer','exists:provincias,id'],
            'departamento_id'  => ['required','integer','exists:departamentos,id'],
            'zona'             => ['nullable','string','max:160'],
            'region'           => ['nullable','string','max:160'],
        ]);

        // Coherencia: la provincia debe pertenecer al departamento
        $provincia = Provincia::find($data['provincia_id']);
        if (!$provincia || $provincia->departamento_id != $data['departamento_id']) {
            return response()->json(['message'=>'La provincia no pertenece al departamento indicado.'], 422);
        }

        // Unicidad dentro de la provincia
        $exists = Municipio::where('provincia_id', $data['provincia_id'])
            ->whereRaw('LOWER(nombre_municipio) = ?', [mb_strtolower($data['nombre_municipio'])])
            ->exists();

        if ($exists) {
            return response()->json(['message'=>'Ya existe un municipio con ese nombre en la provincia.'], 422);
        }

        $mun = Municipio::create($data);
        return response()->json($mun->load(['departamento:id,nombre_departamento','provincia:id,nombre_provincia']), 201);
    }

    public function show(Municipio $municipio)
    {
        return $municipio->load(['departamento:id,nombre_departamento','provincia:id,nombre_provincia,departamento_id']);
    }

    public function update(Request $request, Municipio $municipio)
    {
        $data = $request->validate([
            'nombre_municipio' => ['required','string','max:160'],
            'provincia_id'     => ['required','integer','exists:provincias,id'],
            'departamento_id'  => ['required','integer','exists:departamentos,id'],
            'zona'             => ['nullable','string','max:160'],
            'region'           => ['nullable','string','max:160'],
        ]);

        $provincia = Provincia::find($data['provincia_id']);
        if (!$provincia || $provincia->departamento_id != $data['departamento_id']) {
            return response()->json(['message'=>'La provincia no pertenece al departamento indicado.'], 422);
        }

        $exists = Municipio::where('provincia_id', $data['provincia_id'])
            ->whereRaw('LOWER(nombre_municipio) = ?', [mb_strtolower($data['nombre_municipio'])])
            ->where('id','!=',$municipio->id)
            ->exists();

        if ($exists) {
            return response()->json(['message'=>'Ya existe un municipio con ese nombre en la provincia.'], 422);
        }

        $municipio->update($data);
        return $municipio->load(['departamento:id,nombre_departamento','provincia:id,nombre_provincia']);
    }

    public function destroy(Municipio $municipio)
    {
        $municipio->delete();
        return response()->json(['message'=>'Eliminado'], 200);
    }
}
