<?php

namespace App\Http\Controllers;

use App\Models\Apicultor;
use Illuminate\Http\Request;

class ApicultorController extends Controller
{
    public function index(Request $request)
    {
        $q = Apicultor::query();

        // Filtro de búsqueda libre
        if ($search = $request->get('search')) {
            $q->where(function ($qq) use ($search) {
                $qq->where('codigo_runsa','ilike',"%$search%")
                    ->orWhere('subcodigo','ilike',"%$search%")
                    ->orWhere('runsa','ilike',"%$search%")
                    ->orWhere('nombre_apellido','ilike',"%$search%")
                    ->orWhere('ci','ilike',"%$search%")
                    ->orWhere('asociacion','ilike',"%$search%")
                    ->orWhere('lugar_apiario','ilike',"%$search%");
            });
        }

        // Filtro opcional por asociación (si luego lo usas en el front)
        if ($asoc = $request->get('asociacion')) {
            $q->where('asociacion', $asoc);
        }

        $q->orderBy('id', 'desc');
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'latitud'                           => 'nullable|numeric',
            'longitud'                          => 'nullable|numeric',
            'codigo_runsa'                      => 'nullable|string',
            'subcodigo'                         => 'nullable|string',
            'runsa'                             => 'nullable|string',
            'nombre_apellido'                   => 'required|string',
            'ci'                                => 'nullable|string',
            'expedido'                          => 'nullable|string|max:15',
            'celular'                           => 'nullable|string',
            'lugar_apiario'                     => 'nullable|string',
            'n_colmenas_runsa'                  => 'nullable|integer|min:0',
            'n_colmenas_produccion'             => 'nullable|integer|min:0',
            'produccion_promedio'               => 'nullable|numeric|min:0',
            'proyeccion_produccion_total'       => 'nullable|numeric|min:0',
            'proyeccion_produccion_toneladas'   => 'nullable|numeric|min:0',
            'asociacion'                        => 'nullable|string',
            'fomento'                           => 'nullable|string',
            'fortalecimiento'                   => 'nullable|string',
            'total_beneficiarios'               => 'nullable|integer|min:0',
            'nativas'                           => 'nullable|integer|min:0',
            'fom'                               => 'nullable|integer|min:0',
            'fort'                              => 'nullable|integer|min:0',
            'suma_nuevos'                       => 'nullable|integer|min:0',
            'n_acta'                            => 'nullable|string',
            'lote'                              => 'nullable|string',
            'estado' => 'nullable|in:Activo,Inactivo,Mantenimiento',
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
            'latitud'                           => 'nullable|numeric',
            'longitud'                          => 'nullable|numeric',
            'codigo_runsa'                      => 'nullable|string',
            'subcodigo'                         => 'nullable|string',
            'runsa'                             => 'nullable|string',
            'nombre_apellido'                   => 'sometimes|required|string',
            'ci'                                => 'nullable|string',
            'expedido'                          => 'nullable|string|max:15',
            'celular'                           => 'nullable|string',
            'lugar_apiario'                     => 'nullable|string',
            'n_colmenas_runsa'                  => 'nullable|integer|min:0',
            'n_colmenas_produccion'             => 'nullable|integer|min:0',
            'produccion_promedio'               => 'nullable|numeric|min:0',
            'proyeccion_produccion_total'       => 'nullable|numeric|min:0',
            'proyeccion_produccion_toneladas'   => 'nullable|numeric|min:0',
            'asociacion'                        => 'nullable|string',
            'fomento'                           => 'nullable|string',
            'fortalecimiento'                   => 'nullable|string',
            'total_beneficiarios'               => 'nullable|integer|min:0',
            'nativas'                           => 'nullable|integer|min:0',
            'fom'                               => 'nullable|integer|min:0',
            'fort'                              => 'nullable|integer|min:0',
            'suma_nuevos'                       => 'nullable|integer|min:0',
            'n_acta'                            => 'nullable|string',
            'lote'                              => 'nullable|string',
            'estado' => 'nullable|in:Activo,Inactivo,Mantenimiento',
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
