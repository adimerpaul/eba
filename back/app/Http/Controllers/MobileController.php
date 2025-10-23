<?php

namespace App\Http\Controllers;

use App\Models\Productor;
use App\Models\User;
use Illuminate\Http\Request;

class MobileController extends Controller{
    function export(Request $request){
//        return 'a';
        $users = User::select('id','name','username')
            ->get();
//        $productores = Productor::select('id','nombre','apellidos')
//            ->with('apiarios:id,productor_id,latitud,longitud,lugar_apiario')
////            ->with('apiarios.colmenas:id')
//            ->get();
        $productores = Productor::with(['apiarios'=>function($q){
            $q->select('id','productor_id','latitud','longitud','lugar_apiario')
              ->with(['colmenas'=>function($q2){
                  $q2->select('id','apiario_id');
              }]);
        }])->get(['id','nombre','apellidos']);
        return response()->json([
            'users'=>$users,
            'productores'=>$productores,
        ]);

    }
}
