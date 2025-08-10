<?php

use App\Http\Controllers\ApicultorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CogController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\OrdenPagoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
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

});
