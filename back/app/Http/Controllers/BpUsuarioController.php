<?php

namespace App\Http\Controllers;

use App\Models\BpUsuarios;
use Illuminate\Http\Request;

class BpUsuarioController extends Controller
{
    /**
     * Listar usuarios internos con info de si TRAZA está activado.
     */
    public function index()
    {
        $users = BpUsuarios::select('usr_id', 'usr_usuario', 'usr_estado', 'usr_access_sistem')
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
}
