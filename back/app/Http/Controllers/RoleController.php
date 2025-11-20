<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Listar roles con sus permisos (solo nombres).
     */
    public function index()
    {
        return Role::with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Crear nuevo rol.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create($data);

        return response()->json($role, 201);
    }

    /**
     * Actualizar nombre del rol.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update($data);

        return $role->fresh(['permissions:id,name']);
    }

    /**
     * Eliminar rol (evitar borrar si tiene usuarios).
     */
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un rol que está asignado a usuarios'
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rol eliminado']);
    }

    /**
     * IDs de permisos del rol.
     */
    public function getPermissions(Role $role)
    {
        return $role->permissions()->pluck('id');
    }

    /**
     * Sincronizar permisos del rol.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        error_log('Synchronizing permissions for role ID: ' . json_encode($role));
        $data = $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $perms = Permission::whereIn('id', $data['permissions'] ?? [])->get();
//
        $role->syncPermissions($perms);
//
//        return response()->json([
//            'message'     => 'Permisos del rol actualizados',
//            'permissions' => $role->permissions()->pluck('name'),
//        ]);
    }
}
