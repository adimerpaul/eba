<?php

namespace App\Http\Controllers;

use App\Models\AcopioCosecha;
use Illuminate\Http\Request;

class AcopioCosechaController extends Controller{
    function index(Request $request){
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $estado = $request->input('estado');
        $acopiosCosechas = AcopioCosecha::whereBetween('fecha_cosecha', [$fecha_inicio, $fecha_fin])->with('apiario.productor');
        if($estado){
            $acopiosCosechas = $acopiosCosechas->where('estado', $estado);
        }
        $acopiosCosechas = $acopiosCosechas->get();
        return $acopiosCosechas;
    }
}
