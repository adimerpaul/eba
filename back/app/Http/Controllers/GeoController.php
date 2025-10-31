<?php
// app/Http/Controllers/GeoController.php
namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    // El árbol que ya tienes (lo dejamos igual, si quieres)
    public function tree()
    {
        return Departamento::with([
            'provincias:id,departamento_id,nombre_provincia',
            'provincias.municipios:id,provincia_id,departamento_id,nombre_municipio'
        ])
            ->select('id','nombre_departamento')
            ->orderBy('nombre_departamento')
            ->get();
    }

    // NUEVO: lista plana con contadores
    public function departamentosIndex(Request $request)
    {
        $q = Departamento::query()
            ->select('id','nombre_departamento')
            ->withCount(['provincias','municipios','productores']); // <- aquí sale productores_count

        if ($search = $request->input('search')) {
            $q->where('nombre_departamento', 'like', "%{$search}%");
        }

        return $q->orderBy('nombre_departamento')->get();
    }

    // NUEVO: apiarios por departamento (para el mapa)
    public function apiariosByDepartamento($departamentoId)
    {
        // Solo apiarios con coordenadas válidas
        // Incluimos info útil para el popup
        $rows = \App\Models\Apiario::query()
            ->with(['productor:id,nombre,apellidos','municipio:id,nombre_municipio'])
            ->whereHas('municipio', function($q) use ($departamentoId){
                $q->where('departamento_id', $departamentoId);
            })
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->select('id','productor_id','municipio_id','latitud','longitud','lugar_apiario','nombre_cip','estado')
            ->get()
            ->map(function($a){
                return [
                    'id'         => $a->id,
                    'lat'        => (float) $a->latitud,
                    'lng'        => (float) $a->longitud,
                    'productor'  => $a->productor?->nombre.' '.$a->productor?->apellidos,
                    'municipio'  => $a->municipio?->nombre_municipio,
                    'lugar'      => $a->lugar_apiario,
                    'cip'        => $a->nombre_cip,
                    'estado'     => $a->estado,
                ];
            });

        return response()->json([
            'count'    => $rows->count(),
            'apiarios' => $rows
        ]);
    }
}
