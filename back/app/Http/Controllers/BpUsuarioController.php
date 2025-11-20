<?php

namespace App\Http\Controllers;

use App\Models\BpUsuarios;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class BpUsuarioController extends Controller
{
    /**
     * Listar usuarios internos con info de si TRAZA está activado.
     */
    public function index()
    {
        $users = BpUsuarios::select(
            'usr_id',
            'usr_usuario',
            'usr_estado',
            'usr_access_sistem'
        )
            ->where('usr_estado', 'A') // opcional: solo activos
            ->get()
            ->map(function ($u) {
                $systems = $u->usr_access_sistem ?? [];
                $traza = false;

                foreach ($systems as $s) {
                    if (($s['sistema'] ?? null) === 'TRAZA') {
                        $traza = (bool) ($s['activado'] ?? false);
                        break;
                    }
                }

                // Campo extra para el front
                $u->traza_activado = $traza;

                return $u;
            });

        return response()->json($users->values());
    }

    /**
     * Actualizar el estado del sistema TRAZA en usr_access_sistem.
     */
    public function updateTraza(Request $request, $id)
    {
        $request->validate([
            'activado' => 'required|boolean',
        ]);

        $user = BpUsuarios::findOrFail($id);

        $systems = $user->usr_access_sistem ?? [];
        $found = false;

        // Buscar si ya existe la entrada TRAZA
        foreach ($systems as &$s) {
            if (($s['sistema'] ?? null) === 'TRAZA') {
                $s['activado'] = $request->activado;
                $found = true;
            }
        }
        unset($s);

        // Si no existe, la agregamos
        if (! $found) {
            $systems[] = [
                'sistema'   => 'TRAZA',
                'activado'  => $request->activado,
            ];
        }

        $user->usr_access_sistem = $systems;
        $user->save();

        return response()->json([
            'message'           => 'Permiso TRAZA actualizado',
            'usr_id'            => $user->usr_id,
            'traza_activado'    => $request->activado,
            'usr_access_sistem' => $systems,
        ]);
    }

    /**
     * Obtener IDs de permisos Spatie del usuario interno.
     */
//    public function getPermissions($id)
//    {
//        $user = BpUsuarios::findOrFail($id);
//        return $user->permissions()->pluck('id');
//    }

    /**
     * Sincronizar permisos Spatie del usuario interno.
     */
//    public function syncPermissions(Request $request, $id)
//    {
//        $request->validate([
//            'permissions'   => 'array',
//            'permissions.*' => 'integer|exists:permissions,id',
//        ]);
//
//        $user = BpUsuarios::findOrFail($id);
//        $perms = Permission::whereIn('id', $request->permissions ?? [])->get();
//
//        $user->syncPermissions($perms);
//
//        return response()->json([
//            'message'     => 'Permisos internos actualizados',
//            'permissions' => $user->permissions()->pluck('name'),
//        ]);
//    }
    public function getPermissions($id)
    {
        $user = BpUsuarios::findOrFail($id);
        return $user->permissions()->pluck('id');
    }

    public function syncPermissions(Request $request, $id)
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $user  = BpUsuarios::findOrFail($id);
        $perms = Permission::whereIn('id', $request->permissions ?? [])->get();
        $user->syncPermissions($perms);

        return response()->json([
            'message'     => 'Permisos actualizados',
            'permissions' => $user->permissions()->pluck('name'),
        ]);
    }

    // ---------- NUEVO: ROLES PARA USUARIOS INTERNOS ----------
    public function getRoles($id)
    {
        $user = BpUsuarios::findOrFail($id);
        return $user->roles()->pluck('id');
    }

    public function syncRoles(Request $request, $id)
    {
        $request->validate([
            'roles'   => 'array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $user = BpUsuarios::findOrFail($id);

        $user->syncRoles($request->roles ?? []);

        return response()->json([
            'message'     => 'Roles del usuario interno actualizados',
            'roles'       => $user->roles()->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
