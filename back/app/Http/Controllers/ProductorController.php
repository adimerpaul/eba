<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use Illuminate\Http\Request;

class ProductorController extends Controller
{
    public function index(Request $request)
    {
        $q = Productor::query()
            ->with([
                'municipio:id,nombre_municipio,provincia_id,departamento_id',
                'organizacion:id,nombre_organiza'
            ]);

        // Búsqueda libre
        if ($search = trim((string) $request->get('search', ''))) {
            $q->where(function ($s) use ($search) {
                $s->where('runsa', 'ilike', "%{$search}%")
                    ->orWhere('sub_codigo', 'ilike', "%{$search}%")
                    ->orWhere('nombre', 'ilike', "%{$search}%")
                    ->orWhere('apellidos', 'ilike', "%{$search}%")
                    ->orWhere('numcarnet', 'ilike', "%{$search}%")
                    ->orWhere('comunidad', 'ilike', "%{$search}%")
                    ->orWhere('proveedor', 'ilike', "%{$search}%")
                    ->orWhere('cip_acopio', 'ilike', "%{$search}%")
                    ->orWhere('num_celular', 'ilike', "%{$search}%");
            });
        }

        // Filtros administrativos
        if ($depId = $request->get('departamento_id')) {
            $q->whereHas('municipio', fn($m) => $m->where('departamento_id', $depId));
        }
        if ($provId = $request->get('provincia_id')) {
            $q->whereHas('municipio', fn($m) => $m->where('provincia_id', $provId));
        }
        if ($munId = $request->get('municipio_id')) {
            $q->where('municipio_id', $munId);
        }

        // Otros filtros
        if ($estado = $request->get('estado')) {
            $q->where('estado', $estado);
        }
        if ($sexo = $request->get('sexo')) {
            $q->where('sexo', (int) $sexo);
        }
        if ($orgId = $request->get('organizacion_id')) {
            $q->where('organizacion_id', $orgId);
        }

        // Rango de fechas (registro)
        if ($fd = $request->get('fecha_desde')) {
            $q->whereDate('fecha_registro', '>=', $fd);
        }
        if ($fh = $request->get('fecha_hasta')) {
            $q->whereDate('fecha_registro', '<=', $fh);
        }

        // Ordenación segura
        $allowedSorts = [
            'id','runsa','sub_codigo','nombre','apellidos','numcarnet','fecha_registro','estado'
        ];
        $sortBy = $request->get('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'id';

        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $q->orderBy($sortBy, $sortDir);

        // Paginación (remota)
        $perPage = max(10, min((int) $request->get('per_page', 50), 200)); // 100k registros → paginar 50-100
        return $q->paginate($perPage)->appends($request->query());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'municipio_id'   => ['nullable','integer','exists:municipios,id'],
            'runsa'          => ['nullable','string','max:20'],
            'sub_codigo'     => ['nullable','string','max:20'],
            'nombre'         => ['required','string','max:200'],
            'apellidos'      => ['required','string','max:200'],
            'numcarnet'      => ['required','string','max:20'],
            'expedido'       => ['nullable','string','max:10'],
            'fec_nacimiento' => ['nullable','date'],
            'sexo'           => ['nullable','integer','in:1,2'],
            'direccion'      => ['nullable','string'],
            'comunidad'      => ['nullable','string','max:100'],
            'proveedor'      => ['nullable','string','max:50'],
            'cip_acopio'     => ['nullable','string','max:50'],
            'num_celular'    => ['nullable','string','max:15'],
            'ocupacion'      => ['nullable','string','max:100'],
            'otros'          => ['nullable','string'],
            'seleccion'      => ['nullable','integer','min:0'],
            'organizacion_id'=> ['nullable','integer','exists:organizaciones,id'],
            'fecha_registro' => ['nullable','date'],
            'fecha_expiracion'=>['nullable','date'],
            'estado'         => ['nullable','string','in:VIGENTE,VENCIDO,INACTIVO,ACTIVO'],
        ]);

        $p = Productor::create($data);
        return response()->json($p->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza']), 201);
    }

    public function show(Productor $productor)
    {
        return $productor->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza','certificaciones']);
    }

    public function update(Request $request, Productor $productor)
    {
        $data = $request->validate([
            'municipio_id'   => ['nullable','integer','exists:municipios,id'],
            'runsa'          => ['nullable','string','max:20'],
            'sub_codigo'     => ['nullable','string','max:20'],
            'nombre'         => ['required','string','max:200'],
            'apellidos'      => ['required','string','max:200'],
            'numcarnet'      => ['required','string','max:20'],
            'expedido'       => ['nullable','string','max:10'],
            'fec_nacimiento' => ['nullable','date'],
            'sexo'           => ['nullable','integer','in:1,2'],
            'direccion'      => ['nullable','string'],
            'comunidad'      => ['nullable','string','max:100'],
            'proveedor'      => ['nullable','string','max:50'],
            'cip_acopio'     => ['nullable','string','max:50'],
            'num_celular'    => ['nullable','string','max:15'],
            'ocupacion'      => ['nullable','string','max:100'],
            'otros'          => ['nullable','string'],
            'seleccion'      => ['nullable','integer','min:0'],
            'organizacion_id'=> ['nullable','integer','exists:organizaciones,id'],
            'fecha_registro' => ['nullable','date'],
            'fecha_expiracion'=>['nullable','date'],
            'estado'         => ['nullable','string','in:VIGENTE,VENCIDO,INACTIVO,ACTIVO'],
        ]);

        $productor->update($data);
        return $productor->load(['municipio:id,nombre_municipio,provincia_id,departamento_id','organizacion:id,nombre_organiza']);
    }

    public function destroy(Productor $productor)
    {
        $productor->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
