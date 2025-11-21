<?php

namespace App\Http\Controllers;

use App\Models\Apiario;
use Illuminate\Http\Request;

class ApiarioController extends Controller
{
    public function index(Request $request)
    {
        $q = Apiario::with([
            'municipio.departamento:id,nombre_departamento',
            'productor:id,nombre,apellidos,comunidad,organizacion_id',
            'productor.organizacion:id,nombre_organiza,asociacion'
        ])
        ->leftJoin('acopio_cosechas as ac', function($join) {
            $join->on('apiarios.id', '=', 'ac.apiario_id')
                 ->whereNull('ac.deleted_at');
        })
        ->selectRaw('
            apiarios.*,
            COUNT(DISTINCT ac.id) as cosechas_total,
            COALESCE(SUM(ac.cantidad_kg), 0) as total_kg_historico,
            CASE 
                WHEN COALESCE(apiarios.numero_colmenas_prod, apiarios.numero_colmenas_runsa, 0) > 0 
                THEN ROUND(COALESCE(SUM(ac.cantidad_kg), 0) / COALESCE(apiarios.numero_colmenas_prod, apiarios.numero_colmenas_runsa, 1), 2)
                ELSE 0 
            END as rendimiento_colmenas,
            COALESCE(apiarios.numero_colmenas_prod, apiarios.numero_colmenas_runsa, 0) * 25 as capacidad_productiva
        ')
        ->groupBy('apiarios.id')
        ->whereNotNull('apiarios.id');

        if ($pid = $request->get('productor_id')) $q->where('apiarios.productor_id', $pid);
        if ($mid = $request->get('municipio_id')) $q->where('apiarios.municipio_id', $mid);
        if ($est = $request->get('estado')) $q->where('apiarios.estado', $est);

        $q->orderBy('apiarios.id', 'desc');

        if ($request->boolean('paginate', true)) {
            $per = max(10, min((int)$request->get('per_page', 50), 200));
            return $q->paginate($per)->appends($request->query());
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $apiario = Apiario::create($request->all());
        return response()->json($apiario, 201);
    }

    public function update(Request $request, Apiario $apiario)
    {
        $apiario = Apiario::find($request->id);
        if (!$apiario) return response()->json(['error' => 'Apiario no encontrado'], 404);
        $apiario->update($request->all());
        return $apiario;
    }

    public function destroy($id)
    {
        $apiario = Apiario::find($id);
        $apiario->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }
}
