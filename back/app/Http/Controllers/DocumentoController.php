<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;

class DocumentoController extends Controller
{
    public function index(Request $request)
    {
        $cosechaId = $request->input('cosecha_id');
        $q = Documento::query()->with(['user:id,name,email']);

        if ($cosechaId) {
            $q->where('acopio_cosecha_id', $cosechaId);
        }

        return $q->orderByDesc('id')->get();
    }

    public function show($id)
    {
        return Documento::with(['user:id,name,email'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        // Minimal: sin validaciones (puedes agregar cuando quieras)
        $doc = Documento::create($request->all());
        return response()->json($doc->fresh(['user:id,name,email']), 201);
    }

    public function update(Request $request, $id)
    {
        $doc = Documento::findOrFail($id);
        $doc->update($request->all());
        return $doc->fresh(['user:id,name,email']);
    }

    public function destroy($id)
    {
        $doc = Documento::findOrFail($id);
        $doc->delete();
        return response()->json(['deleted' => true]);
    }
}
