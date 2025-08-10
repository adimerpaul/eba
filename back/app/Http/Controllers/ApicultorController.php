<?php

namespace App\Http\Controllers;

use App\Models\Apicultor;
use Illuminate\Http\Request;

class ApicultorController extends Controller
{
    public function index(Request $request)
    {
        // filtros simples
        $q = Apicultor::query();

        if ($search = $request->get('search')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('codigo','ilike',"%$search%")
                    ->orWhere('nombre','ilike',"%$search%")
                    ->orWhere('ci','ilike',"%$search%")
                    ->orWhere('asociacion','ilike',"%$search%")
                    ->orWhere('municipio','ilike',"%$search%");
            });
        }

        if ($estado = $request->get('estado')) {
            $q->where('estado', $estado);
        }
        if ($depto = $request->get('departamento')) {
            $q->where('departamento', $depto);
        }

        $q->orderBy('id','desc');
//        return $q->paginate($request->get('per_page', 20));
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
//            'codigo' => 'required|string|unique:apicultores,codigo',
            'nombre' => 'required|string',
            'ci' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'departamento' => 'nullable|string',
            'municipio' => 'nullable|string',
            'asociacion' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
            'apiarios' => 'nullable|integer|min:0',
            'ultima_inspeccion' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $apicultor = Apicultor::create($data);
        return response()->json($apicultor, 201);
    }

    public function show(Apicultor $apicultor)
    {
        return $apicultor;
    }

    public function update(Request $request, Apicultor $apicultor)
    {
        $data = $request->validate([
//            'codigo' => "sometimes|required|string|unique:apicultores,codigo,{$apicultor->id}",
            'nombre' => 'sometimes|required|string',
            'ci' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'departamento' => 'nullable|string',
            'municipio' => 'nullable|string',
            'asociacion' => 'nullable|string',
            'estado' => 'nullable|in:Activo,Inactivo',
            'apiarios' => 'nullable|integer|min:0',
            'ultima_inspeccion' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'observaciones' => 'nullable|string',
        ]);

        $apicultor->update($data);
        return $apicultor;
    }

    public function destroy(Apicultor $apicultor)
    {
        $apicultor->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
