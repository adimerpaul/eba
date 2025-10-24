<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use Illuminate\Http\Request;

class AcopioCosechaController extends Controller{
    public function showByQr(string $code)
    {
        $acopio = AcopioCosecha::query()
            ->with(['apiario.productor', 'apiario.municipio', 'producto'])
            ->where('qr_code', $code)
            ->first();

        if (!$acopio) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($acopio);
    }
    function index(Request $request){
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $estado = $request->input('estado');
        $acopiosCosechas = AcopioCosecha::whereBetween('fecha_cosecha', [$fecha_inicio, $fecha_fin])->with('apiario.productor');
        if($estado){
            $acopiosCosechas = $acopiosCosechas->where('estado', $estado);
        }
        $acopiosCosechas = $acopiosCosechas->get();
        return $acopiosCosechas;
    }
    public function show($id)
    {
        return AcopioCosecha::with(['apiario.productor','apiario.municipio','producto'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $qr_code = uniqid();
        $request->merge(['qr_code' => $qr_code]);
        $acopio = AcopioCosecha::create($request->all());
        return response()->json($acopio->fresh(['apiario.productor','apiario.municipio']), 201);
    }

    public function update(Request $request, $id)
    {
        $request->except(['qr_code']);
        $acopio = AcopioCosecha::findOrFail($id);
        $acopio->update($request->all());
        return $acopio->fresh(['apiario.productor','apiario.municipio']);
    }

    public function destroy($id)
    {
        $acopio = AcopioCosecha::findOrFail($id);
        $acopio->delete();
        return response()->json(['deleted' => true]);
    }
}
