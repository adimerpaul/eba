<?php

// app/Http/Controllers/GeoController.php
namespace App\Http\Controllers;

use App\Models\Departamento;

class GeoController extends Controller
{
    public function tree()
    {
        // departamentos -> provincias -> municipios (solo id/nombre para aligerar)
        return Departamento::with([
            'provincias:id,departamento_id,nombre_provincia',
            'provincias.municipios:id,provincia_id,departamento_id,nombre_municipio'
        ])->select('id','nombre_departamento')->orderBy('nombre_departamento')->get();
    }
}
