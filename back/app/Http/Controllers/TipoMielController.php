<?php

namespace App\Http\Controllers;

use App\Models\TipoMiel;
use Illuminate\Http\Request;

class TipoMielController extends Controller
{
    public function index(Request $request)
    {
        $q = TipoMiel::query()->orderBy('tipo_miel');
        if ($s = $request->get('search')) {
            $q->where('tipo_miel', 'ilike', "%{$s}%");
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate(['tipo_miel' => 'required|string|max:50|unique:tipo_miel,tipo_miel']);
        return response()->json(TipoMiel::create($data), 201);
    }

    public function update(Request $request, TipoMiel $tipoMiel)
    {
        $data = $request->validate(['tipo_miel' => 'required|string|max:50|unique:tipo_miel,tipo_miel,'.$tipoMiel->id]);
        $tipoMiel->update($data);
        return $tipoMiel;
    }

    public function destroy(TipoMiel $tipoMiel)
    {
        $tipoMiel->delete();
        return response()->json(['message' => 'Eliminado']);
    }
}
