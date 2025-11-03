<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Listado con búsqueda y (opcional) paginación
     * GET /clientes?q=texto&per_page=20&page=1
     */
    public function index(Request $request)
    {
        $q = Cliente::query()->where('id', '>', 0);

        if ($term = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($term) {
                $w->where('nombre_cliente', 'like', "%{$term}%")
                    ->orWhere('nit', 'like', "%{$term}%")
                    ->orWhere('telefono', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('pais_destino', 'like', "%{$term}%");
            });
        }

        // Solo activos (no soft-deleted)
        $q->whereNull('deleted_at')->orderBy('nombre_cliente');

        // Paginación opcional
        $perPage = (int) $request->get('per_page', 0);
        if ($perPage > 0) {
            return $q->paginate($perPage);
        }

        return $q->get();
    }

    public function show(Cliente $cliente)
    {
        return $cliente;
    }

    public function store(Request $request)
    {
        $cliente = Cliente::create($request->all());
        return response()->json($cliente, 201);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->all());
        return $cliente;
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete(); // Soft delete
        return response()->json(['deleted' => true]);
    }
}
