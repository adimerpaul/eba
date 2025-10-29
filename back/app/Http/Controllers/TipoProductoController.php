<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use Illuminate\Http\Request;

class TipoProductoController extends Controller
{
    public function index()
    {
        return TipoProducto::orderBy('id')->get(['id', 'nombre_tipo']);
    }
}
