<?php

use App\Http\Controllers\ApicultorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CogController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\OrdenPagoController;
use App\Http\Controllers\OrganizacionController;
use App\Http\Controllers\ProductorController;
use App\Http\Controllers\ProvinciaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(callback: function () {
    Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\UserController::class, 'me']);


    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy']);
    Route::put('/updatePassword/{user}', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::post('/{user}/avatar', [App\Http\Controllers\UserController::class, 'updateAvatar']);

    Route::get('/permissions', [App\Http\Controllers\PermissionController::class, 'index']);
    Route::get('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'getPermissions']);
    Route::put('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'syncPermissions']);

    Route::get('/apicultores', [ApicultorController::class, 'index']);
    Route::post('/apicultores', [ApicultorController::class, 'store']);
    Route::get('/apicultores/{apicultor}', [ApicultorController::class, 'show']);
    Route::put('/apicultores/{apicultor}', [ApicultorController::class, 'update']);
    Route::delete('/apicultores/{apicultor}', [ApicultorController::class, 'destroy']);
//    Route::get('/apicultores', [ApicultorController::class,'index'])->middleware('permission:Produccion primaria');

    // Departamentos
    Route::get('/departamentos', [DepartamentoController::class, 'index']);
    Route::post('/departamentos', [DepartamentoController::class, 'store']);
    Route::get('/departamentos/{departamento}', [DepartamentoController::class, 'show']);
    Route::put('/departamentos/{departamento}', [DepartamentoController::class, 'update']);
    Route::delete('/departamentos/{departamento}', [DepartamentoController::class, 'destroy']);

    // Provincias
    Route::get('/provincias', [ProvinciaController::class, 'index']);
    Route::post('/provincias', [ProvinciaController::class, 'store']);
    Route::get('/provincias/{provincia}', [ProvinciaController::class, 'show']);
    Route::put('/provincias/{provincia}', [ProvinciaController::class, 'update']);
    Route::delete('/provincias/{provincia}', [ProvinciaController::class, 'destroy']);

    // Municipios
    Route::get('/municipios', [MunicipioController::class, 'index']);
    Route::post('/municipios', [MunicipioController::class, 'store']);
    Route::get('/municipios/{municipio}', [MunicipioController::class, 'show']);
    Route::put('/municipios/{municipio}', [MunicipioController::class, 'update']);
    Route::delete('/municipios/{municipio}', [MunicipioController::class, 'destroy']);

    // Árbol completo
    Route::get('/geo/tree', [GeoController::class, 'tree']);

    Route::get('/organizaciones', [OrganizacionController::class, 'index']);
    Route::post('/organizaciones', [OrganizacionController::class, 'store']);
    Route::get('/organizaciones/{organizacion}', [OrganizacionController::class, 'show']);
    Route::put('/organizaciones/{organizacion}', [OrganizacionController::class, 'update']);
    Route::delete('/organizaciones/{organizacion}', [OrganizacionController::class, 'destroy']);

    Route::get('/productores', [ProductorController::class, 'index']);
    Route::post('/productores', [ProductorController::class, 'store']);
    Route::get('/productores/{productor}', [ProductorController::class, 'show']);
    Route::put('/productores/{productor}', [ProductorController::class, 'update']);
    Route::delete('/productores/{productor}', [ProductorController::class, 'destroy']);
});
