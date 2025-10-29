<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller{
    function index(){
        return Producto::all();
    }
    function getByTipo($tipo){
        return Producto::where('tipo_id', $tipo)->get();
    }
}
