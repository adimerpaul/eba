<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kardex;

class KardexController extends Controller
{
    //

    public function getKardex(Request $request){
        $res = DB::SELECT('SELECT * FROM fn_kardex_peps(?,?,?)',[$request->producto_id,$request->inicio,$request->fin]);
        return $res;
            return Kardex::where('producto_id', $request->producto_id)->with('producto')
            ->whereDate('fecha_registro', '>=', $request->inicio)
            ->whereDate('fecha_registro', '<=', $request->fin)
            ->get();

    }
}