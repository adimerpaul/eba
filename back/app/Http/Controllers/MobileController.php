<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use App\Models\User;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function export(Request $request)
    {
        $users = User::select('id','name','username')->get();

        $productores = Productor::with([
            'apiarios' => function ($q) {
                $q->select('id','productor_id','latitud','longitud','lugar_apiario')
                    ->with(['colmenas' => function ($q2) {
                        $q2->select('id','apiario_id');
                    }]);
            }
        ])->get([
            'id',
            'municipio_id',
            'runsa',
            'sub_codigo',
            'nombre',
            'apellidos',
            'numcarnet',
            'expedido',
            'fec_nacimiento',
            'sexo',
            'direccion',
            'comunidad',
            'proveedor',
            'cip_acopio',
            'num_celular',
            'ocupacion',
            'otros',
            'seleccion',
            'fecha_registro',
            'organizacion_id',
            'fecha_expiracion',
            'estado',
        ]);

        return response()->json([
            'users'       => $users,
            'productores' => $productores,
        ]);
    }

    public function syncProductor(Request $request)
    {
        $data = $request->validate([
            'numcarnet'   => 'required|string|max:50',
            'nombre'      => 'nullable|string|max:255',
            'apellidos'   => 'nullable|string|max:255',
            'comunidad'   => 'nullable|string|max:255',
            'num_celular' => 'nullable|string|max:50',
            'direccion'   => 'nullable|string|max:255',
            'proveedor'   => 'nullable|string|max:255',
            'estado'      => 'nullable|string|max:50',
            // aquí puedes añadir más campos si quieres sincronizar TODO
        ]);

        $productor = Productor::updateOrCreate(
            ['numcarnet' => $data['numcarnet']],
            $data
        );

        return response()->json([
            'ok'        => true,
            'productor' => $productor,
        ]);
    }
}
