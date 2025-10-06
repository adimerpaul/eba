<?php

namespace App\Http\Controllers;

use App\Models\Colmena;
use Illuminate\Http\Request;

class ColmenaController extends Controller
{
    public function index(Request $request)
    {
        $q = Colmena::with(['tipoMiel:id,tipo_miel', 'apiario:id,productor_id']);
        if ($aid = $request->get('apiario_id')) $q->where('apiario_id', $aid);
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
            'apiario_id' => ['required','integer','exists:apiarios,id'],
            'tipo_miel_id' => ['nullable','integer','exists:tipo_miel,id'],
            'codigo_colmena' => ['nullable','string','max:50','unique:colmenas,codigo_colmena'],
            'tipo_colmena' => ['nullable','string','max:50'],
            'fecha_instalacion' => ['nullable','date'],
            'reina_fecha_nacimiento' => ['nullable','date'],
            'reina_procedencia' => ['nullable','string','max:100'],
            'estado' => ['nullable','in:ACTIVA,INACTIVA'],
        ]);

        $colmena = Colmena::create($data);
        return response()->json($colmena->load('tipoMiel:id,tipo_miel'), 201);
    }

    public function show(Colmena $colmena)
    {
        return $colmena->load('tipoMiel:id,tipo_miel');
    }

    public function update(Request $request, Colmena $colmena)
    {
        $data = $request->validate([
            'apiario_id' => ['required','integer','exists:apiarios,id'],
            'tipo_miel_id' => ['nullable','integer','exists:tipo_miel,id'],
            'codigo_colmena' => ['nullable','string','max:50','unique:colmenas,codigo_colmena,'.$colmena->id],
            'tipo_colmena' => ['nullable','string','max:50'],
            'fecha_instalacion' => ['nullable','date'],
            'reina_fecha_nacimiento' => ['nullable','date'],
            'reina_procedencia' => ['nullable','string','max:100'],
            'estado' => ['nullable','in:ACTIVA,INACTIVA'],
        ]);

        $colmena->update($data);
        return $colmena->load('tipoMiel:id,tipo_miel');
    }

    public function destroy(Colmena $colmena)
    {
        $colmena->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
