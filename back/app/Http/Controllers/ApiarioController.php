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
}
