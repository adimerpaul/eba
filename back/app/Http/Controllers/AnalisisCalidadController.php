<?php

namespace App\Http\Controllers;

use App\Models\AnalisisCalidad;
use Illuminate\Http\Request;

class AnalisisCalidadController extends Controller
{
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $q = AnalisisCalidad::query();
        if ($cosechaId) {
            $q->where('cosecha_id', $cosechaId);
        }
        return $q->orderByDesc('id')->get();
    }

    public function show($id)
    {
        return AnalisisCalidad::findOrFail($id);
    }

    public function store(Request $request)
    {
        $analisis = AnalisisCalidad::create($request->all());
        return response()->json($analisis, 201);
    }

    public function update(Request $request, $id)
    {
        $analisis = AnalisisCalidad::findOrFail($id);
        $analisis->update($request->all());
        return $analisis;
    }

    public function destroy($id)
    {
        $analisis = AnalisisCalidad::findOrFail($id);
        $analisis->delete();
        return response()->json(['deleted' => true]);
    }
}
