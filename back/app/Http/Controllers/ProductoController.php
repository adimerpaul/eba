<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller{
    public function index(Request $request)
    {
        $q = Producto::query();

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('nombre_producto', 'like', "%{$term}%")
                    ->orWhere('codigo_producto', 'like', "%{$term}%")
                    ->orWhere('codigo_barra', 'like', "%{$term}%");
            });
        }

        if ($request->filled('tipo')) {
            $q->where('tipo_id', (int) $request->get('tipo'));
        }

        $q->whereNull('deleted_at')->orderBy('nombre_producto');

        // Si quieres paginar, habilita esto:
        // $perPage = (int) ($request->get('per_page', 0));
        // if ($perPage > 0) return $q->paginate($perPage);

        return $q->get();
    }
    function getByTipo($tipo){
        return Producto::where('tipo_id', $tipo)->get();
    }
    public function show(Producto $producto)
    {
        return $producto->load('tipo');
    }
    public function store(Request $request)
    {
//        $data = $request->validated();
        $data = $request->all();
        if (!isset($data['presentacion'])) {
            $data['presentacion'] = 'PIEZA';
        }
        $producto = Producto::create($data);
        return response()->json($producto->fresh('tipo'), 201);
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->validated());
        return $producto->fresh('tipo');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }

    /**
     * Subida de imagen (guarda en storage/public/productos y setea el campo 'imagen')
     */
    public function uploadImage(Request $request, Producto $producto)
    {
        $request->validate([
            'file' => ['required','image','max:4096'] // 4 MB
        ]);

        $path = $request->file('file')->store('productos', 'public');
        $producto->update(['imagen' => $path]);

        return response()->json([
            'ok'       => true,
            'imagen'   => $path,
            'producto' => $producto->fresh()
        ]);
    }
}
