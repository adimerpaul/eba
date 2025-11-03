<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kardex;

class KardexController extends Controller
{
    //

    public function getKardex(Request $request){
            return Kardex::where('producto_id', $request->producto_id)->with('producto')
            ->whereDate('fecha_registro', '>=', $request->inicio)
            ->whereDate('fecha_registro', '<=', $request->fin)
            ->get();

    }
}