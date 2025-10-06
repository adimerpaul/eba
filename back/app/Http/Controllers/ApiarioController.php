<?php

namespace App\Http\Controllers;

use App\Models\Apiario;
use Illuminate\Http\Request;

class ApiarioController extends Controller
{
    public function index(Request $request)
    {
        $q = Apiario::with([
            'municipio:id,nombre_municipio',
        ])->whereNotNull('id');

        if ($pid = $request->get('productor_id')) $q->where('productor_id', $pid);
        if ($mid = $request->get('municipio_id')) $q->where('municipio_id', $mid);
        if ($est = $request->get('estado')) $q->where('estado', $est);

        $q->orderBy('id', 'desc');

        if ($request->boolean('paginate', true)) {
            $per = max(10, min((int)$request->get('per_page', 50), 200));
            return $q->paginate($per)->appends($request->query());
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'productor_id' => ['required','integer','exists:productores,id'],
            'municipio_id' => ['nullable','integer','exists:municipios,id'],
            'nombre_cip'   => ['nullable','string','max:100'],
            'latitud'      => ['nullable','numeric','between:-90,90'],
            'longitud'     => ['nullable','numeric','between:-180,180'],
            'altitud'      => ['nullable','integer'],
            'lugar_apiario'=> ['nullable','string','max:200'],
            'numero_colmenas_runsa' => ['nullable','integer'],
            'numero_colmenas_prod'  => ['nullable','integer'],
            'seleccion'    => ['nullable','integer'],
            'rend_programa_nal' => ['nullable','numeric'],
            'organizacion_id' => ['nullable','integer'],
            'fecha_instalacion' => ['nullable','date'],
            'estado'       => ['nullable'],
            'fase'         => ['nullable','string','max:50'],
            'coordenada'   => ['nullable','string','max:50'],
        ]);

        $apiario = Apiario::create($data);
        return response()->json($apiario->load('municipio:id,nombre_municipio'), 201);
    }

    public function show(Apiario $apiario)
    {
        return $apiario->load([
            'municipio:id,nombre_municipio',
            'colmenas.tipoMiel:id,tipo_miel'
        ]);
    }

    public function update(Request $request, Apiario $apiario)
    {
        $data = $request->validate([
            'productor_id' => ['required','integer','exists:productores,id'],
            'municipio_id' => ['nullable','integer','exists:municipios,id'],
            'nombre_cip'   => ['nullable','string','max:100'],
            'latitud'      => ['nullable','numeric','between:-90,90'],
            'longitud'     => ['nullable','numeric','between:-180,180'],
            'altitud'      => ['nullable','integer'],
            'lugar_apiario'=> ['nullable','string','max:200'],
            'numero_colmenas_runsa' => ['nullable','integer'],
            'numero_colmenas_prod'  => ['nullable','integer'],
            'seleccion'    => ['nullable','integer'],
            'rend_programa_nal' => ['nullable','numeric'],
            'organizacion_id' => ['nullable','integer'],
            'fecha_instalacion' => ['nullable','date'],
            'estado'       => ['nullable','in:ACTIVO,INACTIVO'],
            'fase'         => ['nullable','string','max:50'],
            'coordenada'   => ['nullable','string','max:50'],
        ]);

        $apiario->update($data);
        return $apiario->load('municipio:id,nombre_municipio');
    }

    public function destroy(Apiario $apiario)
    {
        $apiario->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
