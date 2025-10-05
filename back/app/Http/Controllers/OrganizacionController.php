<?php

namespace App\Http\Controllers;

use App\Models\Organizacion;
use App\Models\Municipio;
use Illuminate\Http\Request;

class OrganizacionController extends Controller
{
    public function index(Request $request)
    {
        $q = Organizacion::with([
            // Esta relación no hace join con otra tabla, así que 'id' no es ambiguo
            'municipio:id,nombre_municipio,provincia_id,departamento_id',

            // ✅ Calificamos columnas con el nombre de la tabla para evitar ambigüedad
            'provincia' => function ($qq) {
                $qq->select('provincias.id', 'provincias.nombre_provincia', 'provincias.departamento_id');
            },
            'departamento' => function ($qq) {
                $qq->select('departamentos.id', 'departamentos.nombre_departamento');
            },
        ]);

        if ($search = trim((string) $request->get('search', ''))) {
            $q->where(function ($s) use ($search) {
                $s->where('nombre_organiza', 'ilike', "%{$search}%")
                    ->orWhere('asociacion', 'ilike', "%{$search}%")
                    ->orWhere('programa', 'ilike', "%{$search}%")
                    ->orWhere('nombre_presidente', 'ilike', "%{$search}%")
                    ->orWhere('descripcion', 'ilike', "%{$search}%")
                    ->orWhere('celular', 'ilike', "%{$search}%");
            });
        }

        if ($depId = $request->get('departamento_id')) {
            $q->whereHas('municipio', fn ($m) => $m->where('departamento_id', $depId));
        }
        if ($provId = $request->get('provincia_id')) {
            $q->whereHas('municipio', fn ($m) => $m->where('provincia_id', $provId));
        }
        if ($munId = $request->get('municipio_id')) {
            $q->where('municipio_id', $munId);
        }
        if ($estado = $request->get('estado')) {
            $q->where('estado', $estado);
        }

        $q->orderBy('id', 'desc');

        if ($request->boolean('paginate', false)) {
            $perPage = max(5, min((int) $request->get('per_page', 20), 100));
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'municipio_id'      => ['nullable', 'integer', 'exists:municipios,id'],
            'nombre_organiza'   => ['required', 'string', 'max:50'],
            'asociacion'        => ['required', 'string', 'max:50'],
            'programa'          => ['nullable', 'string', 'max:50'],
            'nombre_presidente' => ['nullable', 'string', 'max:50'],
            'descripcion'       => ['nullable', 'string', 'max:250'],
            'celular'           => ['nullable', 'string', 'max:20'],
            'num_apicultor'     => ['nullable', 'integer', 'min:0'],
            'num_colmena'       => ['nullable', 'integer', 'min:0'],
            'pj_actual'         => ['nullable', 'integer', 'min:0'],
            'convenio'          => ['nullable', 'integer', 'min:0'],
            'estado'            => ['nullable', 'in:ACTIVO,INACTIVO'],
            'fecha_registro'    => ['nullable', 'date'],
        ]);

        if (!empty($data['municipio_id']) && !Municipio::whereKey($data['municipio_id'])->exists()) {
            return response()->json(['message' => 'Municipio no válido'], 422);
        }

        $org = Organizacion::create($data);

        // ✅ Misma corrección en los eager loads
        return response()->json(
            $org->load([
                'municipio:id,nombre_municipio,provincia_id,departamento_id',
                'provincia' => function ($qq) {
                    $qq->select('provincias.id', 'provincias.nombre_provincia', 'provincias.departamento_id');
                },
                'departamento' => function ($qq) {
                    $qq->select('departamentos.id', 'departamentos.nombre_departamento');
                },
            ]),
            201
        );
    }

    public function show(Organizacion $organizacion)
    {
        return $organizacion->load([
            'municipio:id,nombre_municipio,provincia_id,departamento_id',
            'provincia' => function ($qq) {
                $qq->select('provincias.id', 'provincias.nombre_provincia', 'provincias.departamento_id');
            },
            'departamento' => function ($qq) {
                $qq->select('departamentos.id', 'departamentos.nombre_departamento');
            },
        ]);
    }

    public function update(Request $request, Organizacion $organizacion)
    {
        $data = $request->validate([
            'municipio_id'      => ['nullable', 'integer', 'exists:municipios,id'],
            'nombre_organiza'   => ['required', 'string', 'max:50'],
            'asociacion'        => ['required', 'string', 'max:50'],
            'programa'          => ['nullable', 'string', 'max:50'],
            'nombre_presidente' => ['nullable', 'string', 'max:50'],
            'descripcion'       => ['nullable', 'string', 'max:250'],
            'celular'           => ['nullable', 'string', 'max:20'],
            'num_apicultor'     => ['nullable', 'integer', 'min:0'],
            'num_colmena'       => ['nullable', 'integer', 'min:0'],
            'pj_actual'         => ['nullable', 'integer', 'min:0'],
            'convenio'          => ['nullable', 'integer', 'min:0'],
            'estado'            => ['nullable', 'in:ACTIVO,INACTIVO'],
            'fecha_registro'    => ['nullable', 'date'],
        ]);

        $organizacion->update($data);

        return $organizacion->load([
            'municipio:id,nombre_municipio,provincia_id,departamento_id',
            'provincia' => function ($qq) {
                $qq->select('provincias.id', 'provincias.nombre_provincia', 'provincias.departamento_id');
            },
            'departamento' => function ($qq) {
                $qq->select('departamentos.id', 'departamentos.nombre_departamento');
            },
        ]);
    }

    public function destroy(Organizacion $organizacion)
    {
        $organizacion->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
