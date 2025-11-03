<?php

use App\Http\Controllers\AcopioCosechaController;
use App\Http\Controllers\AnalisisCalidadController;
use App\Http\Controllers\ApicultorController;
use App\Http\Controllers\CertificacionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CogController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\OrdenPagoController;
use App\Http\Controllers\OrganizacionController;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProductorController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\KardexController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);
Route::get('/cosechas/qr/{code}', [AcopioCosechaController::class, 'showByQr']);
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
    Route::get('geo/tree', [\App\Http\Controllers\GeoController::class, 'tree']);
    Route::get('geo/departamentos', [\App\Http\Controllers\GeoController::class, 'departamentosIndex']);
    Route::get('geo/departamentos/{id}/apiarios', [\App\Http\Controllers\GeoController::class, 'apiariosByDepartamento']);


    Route::get('/organizaciones', [OrganizacionController::class, 'index']);
    Route::post('/organizaciones', [OrganizacionController::class, 'store']);
    Route::get('/organizaciones/{organizacion}', [OrganizacionController::class, 'show']);
    Route::put('/organizaciones/{organizacion}', [OrganizacionController::class, 'update']);
    Route::delete('/organizaciones/{organizacion}', [OrganizacionController::class, 'destroy']);
    Route::post('/uploadFileUrl/{organizacion}', [OrganizacionController::class, 'uploadFileUrl']);

    Route::get('/productores', [ProductorController::class, 'index']);
    Route::post('/productores', [ProductorController::class, 'store']);
    Route::get('/productores/{productor}', [ProductorController::class, 'show']);
    Route::put('/productores/{productor}', [ProductorController::class, 'update']);
    Route::delete('/productores/{productor}', [ProductorController::class, 'destroy']);

//    Route::get('/certificaciones', [CertificacionController::class, 'index']);
    Route::post('/certificaciones', [CertificacionController::class, 'store']);
//    Route::get('/certificaciones/{certificacion}', [CertificacionController::class, 'show']);
    Route::put('/certificaciones/{certificacion}', [CertificacionController::class, 'update']);
    Route::delete('/certificaciones/{certificacion}', [CertificacionController::class, 'destroy']);
//    Route::get('/productores/{productor}/certificaciones/print', [CertificacionController::class, 'printByProductor']);
    Route::get('/certificaciones/{certificacion}/print', [CertificacionController::class, 'print']);

    // Tipo miel
    Route::get('/tipo-miel', [\App\Http\Controllers\TipoMielController::class, 'index']);
    Route::post('/tipo-miel', [\App\Http\Controllers\TipoMielController::class, 'store']);
    Route::put('/tipo-miel/{tipoMiel}', [\App\Http\Controllers\TipoMielController::class, 'update']);
    Route::delete('/tipo-miel/{tipoMiel}', [\App\Http\Controllers\TipoMielController::class, 'destroy']);

// Apiarios
    Route::get('/apiarios', [\App\Http\Controllers\ApiarioController::class, 'index']);
    Route::post('/acopio-cosechas', [AcopioCosechaController::class, 'store']);
    Route::get('/acopio-cosechas/{id}', [AcopioCosechaController::class, 'show']);
    Route::put('/acopio-cosechas/{id}', [AcopioCosechaController::class, 'update']);
    Route::delete('/acopio-cosechas/{id}', [AcopioCosechaController::class, 'destroy']);

//    Productos
    Route::get('/productos', [\App\Http\Controllers\ProductoController::class, 'index']);
    Route::get('/productos/tipo/{tipo}', [\App\Http\Controllers\ProductoController::class, 'getByTipo']);

// Colmenas
    Route::get('/colmenas', [\App\Http\Controllers\ColmenaController::class, 'index']);
    Route::post('/colmenas', [\App\Http\Controllers\ColmenaController::class, 'store']);
    Route::get('/colmenas/{colmena}', [\App\Http\Controllers\ColmenaController::class, 'show']);
    Route::put('/colmenas/{colmena}', [\App\Http\Controllers\ColmenaController::class, 'update']);
    Route::delete('/colmenas/{colmena}', [\App\Http\Controllers\ColmenaController::class, 'destroy']);

    Route::get('/acopio/cosechas', [\App\Http\Controllers\AcopioCosechaController::class, 'index']);

    Route::get('/analisis-calidad', [AnalisisCalidadController::class, 'index']);
    Route::post('/analisis-calidad', [AnalisisCalidadController::class, 'store']);
    Route::get('/analisis-calidad/{id}', [AnalisisCalidadController::class, 'show']);
    Route::put('/analisis-calidad/{id}', [AnalisisCalidadController::class, 'update']);
    Route::delete('/analisis-calidad/{id}', [AnalisisCalidadController::class, 'destroy']);

    Route::get('/documentos', [DocumentoController::class, 'index']);
    Route::post('/documentos', [DocumentoController::class, 'store']);
    Route::get('/documentos/{id}', [DocumentoController::class, 'show']);
    Route::put('/documentos/{id}', [DocumentoController::class, 'update']);
    Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy']);

    Route::get('acopio-cosechas/{cosecha}/lotes', [LoteController::class, 'index']);
    Route::post('acopio-cosechas/{cosecha}/lotes', [LoteController::class, 'store']);
    Route::put('lotes/{lote}', [LoteController::class, 'update']);
    Route::delete('lotes/{lote}', [LoteController::class, 'destroy']);

    Route::get('/tanques', [\App\Http\Controllers\TanqueController::class, 'index']);


    Route::get('productos', [ProductoController::class, 'index']);          // ?q=...&tipo=3
    Route::get('productos/{producto}', [ProductoController::class, 'show']);
    Route::post('productos', [ProductoController::class, 'store']);
    Route::put('productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('productos/{producto}', [ProductoController::class, 'destroy']);

// Subida de imagen (opcional)
    Route::post('productos/{producto}/imagen', [ProductoController::class, 'uploadImage']);

// Tipos de producto para el combo
    Route::get('tipo-productos', [TipoProductoController::class, 'index']);


    Route::get('clientes', [ClienteController::class, 'index']);          // ?q=...&per_page=...
    Route::get('clientes/{cliente}', [ClienteController::class, 'show']);
    Route::post('clientes', [ClienteController::class, 'store']);
    Route::put('clientes/{cliente}', [ClienteController::class, 'update']);
    Route::delete('clientes/{cliente}', [ClienteController::class, 'destroy']);

    Route::get('transportes', [TransporteController::class, 'index']);          // ?q=...&per_page=...
    Route::get('transportes/{transporte}', [TransporteController::class, 'show']);
    Route::post('transportes', [TransporteController::class, 'store']);
    Route::put('transportes/{transporte}', [TransporteController::class, 'update']);
    Route::delete('transportes/{transporte}', [TransporteController::class, 'destroy']);

    Route::get('plantas', [PlantaController::class, 'index']);          // ?q=...&municipio_id=...&per_page=...
    Route::get('plantas/{planta}', [PlantaController::class, 'show']);
    Route::post('plantas', [PlantaController::class, 'store']);
    Route::put('plantas/{planta}', [PlantaController::class, 'update']);
    Route::delete('plantas/{planta}', [PlantaController::class, 'destroy']);

    Route::get('lotes/disponibles', [VentaController::class, 'lotesDisponibles']);

    Route::get('ventas', [VentaController::class, 'index']);       // opcional: listado
    Route::get('ventas/{venta}', [VentaController::class, 'show']); // detalle
    Route::post('ventas', [VentaController::class, 'store']);       // crear
    Route::delete('ventas/{venta}', [VentaController::class, 'destroy']); // opcional
    Route::get('/organizaciones/reportActivos/{estado}', [OrganizacionController::class, 'reportActivos']);

    Route::post('productorExcel', [ProductorController::class, 'productorExcel']);
    Route::post('acopioExcel', [AcopioCosechaController::class, 'acopioExcel']);
    Route::post('productoExcel', [ProductoController::class, 'productoExcel']);
    Route::post('getKardex', [KardexController::class, 'getKardex']);
});
Route::get('/documentos/{id}/imprimir', [DocumentoController::class, 'printDocument']);
Route::get('/ventas/{venta}/nota', [VentaController::class, 'notaPdf'])->name('ventas.nota');

Route::get('/export', [MobileController::class, 'export']);
// PDF por productor
//Route::get('/productores/{productor}/certificaciones/print', [CertificacionController::class, 'printByProductor']);
