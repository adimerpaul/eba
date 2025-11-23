<?php

use App\Http\Controllers\AcopioCosechaController;
use App\Http\Controllers\AnalisisCalidadController;
use App\Http\Controllers\ApicultorController;
use App\Http\Controllers\BpUsuarioController;
use App\Http\Controllers\CertificacionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CogController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DashboardTrazabilidadController;
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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\TransporteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RunsaController;
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

    // ---------- ROLES ----------
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::put('roles/{role}', [RoleController::class, 'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    Route::get('roles/{role}/permissions', [RoleController::class, 'getPermissions']);
    Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

    // ---------- ROLES POR USUARIO EXTERNO ----------
    Route::get('users/{user}/roles', [App\Http\Controllers\UserController::class, 'getRoles']);
    Route::put('users/{user}/roles', [App\Http\Controllers\UserController::class, 'syncRoles']);

    // ---------- ROLES POR USUARIO INTERNO (BpUsuarios) ----------
    Route::get('bp-usuarios/{id}/roles', [BpUsuarioController::class, 'getRoles']);
    Route::put('bp-usuarios/{id}/roles', [BpUsuarioController::class, 'syncRoles']);

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
    // 2025-11-21: Endpoint para verificar vencimientos proximos (30 dias)
    Route::get('/productores/{productor}/verificar-vencimientos', [ProductorController::class, 'verificarVencimientos']);
    // 2025-11-21: Endpoint para verificar duplicados antes de crear productor
    Route::get('/productores-verificar-duplicado', [ProductorController::class, 'verificarDuplicado']);
    // NUEVO: Endpoint para obtener acopios mensuales por gestión de un productor
    // Ejemplo: GET /api/productores/11613/acopios-gestion?gestion=2025&producto_id=1
    Route::get('/productores/{productor}/acopios-gestion', [ProductorController::class, 'acopiosMensualesGestion']);
    // NUEVO: Endpoint para exportar acopios mensuales por gestión a Excel
    // Ejemplo: POST /api/productores/11613/acopios-gestion-excel con body: {gestion: 2025, producto_id: 1}
    Route::post('/productores/{productor}/acopios-gestion-excel', [ProductorController::class, 'acopiosGestionExcel']);
    // NUEVO: Endpoint para obtener acopios de multiples productores en lote (tabla principal)
    // Ejemplo: POST /api/productores/acopios-gestion-lote con body: {productor_ids: [1,2,3], gestion: 2025, producto_id: 1}
    Route::post('productores/acopios-gestion-lote', [ProductorController::class, 'acopiosGestionLote']);
    // 2025-11-23: Endpoint para generar reporte individual de productor en PDF
    Route::get('/productores/{productor}/reporte', [ProductorController::class, 'generarReporteIndividual']);

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
    Route::put('/apiarios/{id}', [\App\Http\Controllers\ApiarioController::class, 'update']);
    Route::post('/apiarios', [\App\Http\Controllers\ApiarioController::class, 'store']);
    Route::delete('/apiarios/{id}', [\App\Http\Controllers\ApiarioController::class, 'destroy']);
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
    Route::post('productorAcopios', [AcopioCosechaController::class, 'productorAcopios']);
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

    // Rutas para registros de control de plagas en colmenas
    Route::get('/plagas', [\App\Http\Controllers\PlagaController::class, 'index']);
    Route::post('/plagas', [\App\Http\Controllers\PlagaController::class, 'store']);
    Route::get('/plagas/{id}', [\App\Http\Controllers\PlagaController::class, 'show']);
    Route::put('/plagas/{id}', [\App\Http\Controllers\PlagaController::class, 'update']);
    Route::delete('/plagas/{id}', [\App\Http\Controllers\PlagaController::class, 'destroy']);

    // Rutas para registros de limpieza y desinfección
    Route::get('/limpiezas', [\App\Http\Controllers\LimpiezaController::class, 'index']);
    Route::post('/limpiezas', [\App\Http\Controllers\LimpiezaController::class, 'store']);
    Route::get('/limpiezas/{id}', [\App\Http\Controllers\LimpiezaController::class, 'show']);
    Route::put('/limpiezas/{id}', [\App\Http\Controllers\LimpiezaController::class, 'update']);
    Route::delete('/limpiezas/{id}', [\App\Http\Controllers\LimpiezaController::class, 'destroy']);

    // Rutas para registros de aplicación de medicamentos
    Route::get('/medicamentos', [\App\Http\Controllers\MedicamentoController::class, 'index']);
    Route::post('/medicamentos', [\App\Http\Controllers\MedicamentoController::class, 'store']);
    Route::get('/medicamentos/{id}', [\App\Http\Controllers\MedicamentoController::class, 'show']);
    Route::put('/medicamentos/{id}', [\App\Http\Controllers\MedicamentoController::class, 'update']);
    Route::delete('/medicamentos/{id}', [\App\Http\Controllers\MedicamentoController::class, 'destroy']);

    Route::get('acopio-cosechas/{cosecha}/lotes', [LoteController::class, 'index']);
    Route::post('acopio-cosechas/{cosecha}/lotes', [LoteController::class, 'store']);
    Route::put('lotes/{lote}', [LoteController::class, 'update']);
    Route::delete('lotes/{lote}', [LoteController::class, 'destroy']);
    Route::get('lotes/trazabilidad', [LoteController::class, 'trazabilidad']);

    Route::get('/tanques/estadisticas', [\App\Http\Controllers\TanqueController::class, 'estadisticas']);
    Route::get('/tanques', [\App\Http\Controllers\TanqueController::class, 'index']);
    Route::get('/tanques/{id}', [\App\Http\Controllers\TanqueController::class, 'show']);
    Route::post('/tanques', [\App\Http\Controllers\TanqueController::class, 'store']);
    Route::put('/tanques/{id}', [\App\Http\Controllers\TanqueController::class, 'update']);
    Route::delete('/tanques/{id}', [\App\Http\Controllers\TanqueController::class, 'destroy']);
    Route::get('/tanques/{id}/ocupacion', [\App\Http\Controllers\TanqueController::class, 'ocupacion']);
    Route::get('/tanques/{id}/historial', [\App\Http\Controllers\TanqueController::class, 'historial']);

    // Rutas para control de transporte de acopios (trazabilidad de entrada)
    Route::get('acopio-cosechas/{acopioId}/transporte-logs', [\App\Http\Controllers\AcopioTransporteLogController::class, 'index']);
    Route::post('acopio-cosechas/{acopioId}/transporte-logs', [\App\Http\Controllers\AcopioTransporteLogController::class, 'store']);
    Route::get('transporte-logs/{id}', [\App\Http\Controllers\AcopioTransporteLogController::class, 'show']);
    Route::put('transporte-logs/{id}', [\App\Http\Controllers\AcopioTransporteLogController::class, 'update']);
    Route::delete('transporte-logs/{id}', [\App\Http\Controllers\AcopioTransporteLogController::class, 'destroy']);
    Route::get('transporte-logs-estadisticas', [\App\Http\Controllers\AcopioTransporteLogController::class, 'estadisticas']);
    Route::get('transporte-logs-alertas', [\App\Http\Controllers\AcopioTransporteLogController::class, 'alertas']);

    Route::get('productos', [ProductoController::class, 'index']);          // ?q=...&tipo=3
    Route::get('productos/{producto}', [ProductoController::class, 'show']);
    Route::post('productos', [ProductoController::class, 'store']);
    Route::put('productos/{id}', [ProductoController::class, 'update']);
    Route::delete('productos/{id}', [ProductoController::class, 'destroy']);

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
    Route::get('transportes/{transporte}/estadisticas-completas', [TransporteController::class, 'estadisticasCompletas']);
    Route::post('transportes', [TransporteController::class, 'store']);
    Route::put('transportes/{transporte}', [TransporteController::class, 'update']);
    Route::delete('transportes/{transporte}', [TransporteController::class, 'destroy']);

    Route::get('plantas', [PlantaController::class, 'index']);          // ?q=...&municipio_id=...&per_page=...
    Route::get('plantas/{planta}', [PlantaController::class, 'show']);
    Route::post('plantas', [PlantaController::class, 'store']);
    Route::put('plantas/{planta}', [PlantaController::class, 'update']);
    Route::delete('plantas/{planta}', [PlantaController::class, 'destroy']);

    Route::get('lotes/disponibles', [VentaController::class, 'lotesDisponibles']);

    Route::get('ventas/estadisticas', [VentaController::class, 'estadisticas']);
    Route::get('ventas', [VentaController::class, 'index']);       // opcional: listado
    Route::get('ventas/{venta}', [VentaController::class, 'show']); // detalle
    Route::post('ventas', [VentaController::class, 'store']);       // crear
    Route::delete('ventas/{venta}', [VentaController::class, 'destroy']); // opcional
    
    Route::prefix('dashboard-trazabilidad')->group(function () {
        Route::get('capacidad-productiva', [DashboardTrazabilidadController::class, 'capacidadProductiva']);
        Route::get('alertas-sobreproduccion', [DashboardTrazabilidadController::class, 'alertasSobreproduccion']);
        Route::get('senasag', [DashboardTrazabilidadController::class, 'estadisticasSenasag']);
        Route::get('transporte-apiarios', [DashboardTrazabilidadController::class, 'transporteApiarios']);
        Route::get('recepcion-rechazos', [DashboardTrazabilidadController::class, 'recepcionRechazos']);
        Route::get('procesamiento-merma', [DashboardTrazabilidadController::class, 'procesamientoMerma']);
        Route::get('lotes-almacenamiento', [DashboardTrazabilidadController::class, 'lotesAlmacenamiento']);
        Route::get('metodos-procesamiento', [DashboardTrazabilidadController::class, 'metodosProcessamiento']);
        Route::get('mortandad', [DashboardTrazabilidadController::class, 'mortandad']);
    });
    
    Route::get('/organizaciones/reportActivos/{estado}', [OrganizacionController::class, 'reportActivos']);

    Route::post('productorExcel', [ProductorController::class, 'productorExcel']);
    Route::post('acopioExcel', [AcopioCosechaController::class, 'acopioExcel']);
    Route::post('productoExcel', [ProductoController::class, 'productoExcel']);
    Route::post('ventaExcel', [VentaController::class, 'ventaExcel']);

    Route::post('getKardex', [KardexController::class, 'getKardex']);

    Route::post('/reporteAcopioProveedorDep', [ReporteController::class, 'reporteAcopioProveedorDep']); //reporteAcopioProveedorDep
    Route::post('/reporteAcopioProveedorMun', [ReporteController::class, 'reporteAcopioProveedorMun']); //reporteAcopioProveedorMun
    Route::post('/reportePorcentual', [ReporteController::class, 'reportePorcentual']); //reportePorcentual
    Route::post('/reportEdad', [ReporteController::class, 'reportEdad']); //reportEdad
    Route::post('/reportAcopioOrg', [ReporteController::class, 'reportAcopioOrg']); //reportAcopioOrg
    Route::post('/reportApicultorDep', [ReporteController::class, 'reportApicultorDep']); //reportApicultorDep
    Route::post('/reportApicultorDepGenero', [ReporteController::class, 'reportApicultorDepGenero']); //reportApicultorDepGenero
    Route::post('/reportePorcentualApicultorDep', [ReporteController::class, 'reportePorcentualApicultorDep']); //reportePorcentualApicultorDep
    Route::post('/reportePorcentualColmenasDep', [ReporteController::class, 'reportePorcentualColmenasDep']); //reportePorcentualColmenasDep
    Route::post('/reportePorcentualApicultorDepAcopio', [ReporteController::class, 'reportePorcentualApicultorDepAcopio']); //reportePorcentualApicultorDepAcopio
    Route::post('/reportePorcentualApicultorDepAcopio2', [ReporteController::class, 'reportePorcentualApicultorDepAcopio2']); //reportePorcentualApicultorDepAcopio2


    Route::get('/runsas', [RunsaController::class, 'index']);
    Route::post('/runsas', [RunsaController::class, 'store']);
    Route::put('/runsas/{runsa}', [RunsaController::class, 'update']);
    Route::delete('/runsas/{runsa}', [RunsaController::class, 'destroy']);

    // Usuarios internos
    Route::get('bp-usuarios', [BpUsuarioController::class, 'index']);
    Route::put('bp-usuarios/{id}/traza', [BpUsuarioController::class, 'updateTraza']);

// Permisos de BpUsuarios
    Route::get('bp-usuarios/{id}/permissions', [BpUsuarioController::class, 'getPermissions']);
    Route::put('bp-usuarios/{id}/permissions', [BpUsuarioController::class, 'syncPermissions']);

    Route::post('anularVenta', [VentaController::class, 'anularVenta']);

    Route::get('control-procesos', [\App\Http\Controllers\ControlProcesoController::class, 'index']);
    Route::post('control-procesos', [\App\Http\Controllers\ControlProcesoController::class, 'store']);
    Route::get('control-procesos/{id}', [\App\Http\Controllers\ControlProcesoController::class, 'show']);
    Route::put('control-procesos/{id}', [\App\Http\Controllers\ControlProcesoController::class, 'update']);
    Route::delete('control-procesos/{id}', [\App\Http\Controllers\ControlProcesoController::class, 'destroy']);
    Route::put('control-procesos/{id}/finalizar', [\App\Http\Controllers\ControlProcesoController::class, 'finalizarProceso']);
    Route::get('control-procesos-finalizados', [\App\Http\Controllers\ControlProcesoController::class, 'finalizados']);
    Route::get('acopios-disponibles-proceso', [AcopioCosechaController::class, 'disponiblesParaProceso']);
    
    // Procesamiento masivo de acopios
    Route::get('procesamiento-masivo/acopios-disponibles', [\App\Http\Controllers\ProcesamientoMasivoController::class, 'acopiosDisponibles']);
    Route::post('procesamiento-masivo/procesar', [\App\Http\Controllers\ProcesamientoMasivoController::class, 'procesarMasivo']);
    Route::post('procesamiento-masivo/rechazar', [\App\Http\Controllers\ProcesamientoMasivoController::class, 'rechazarAcopios']);
    Route::get('procesamiento-masivo/estadisticas', [\App\Http\Controllers\ProcesamientoMasivoController::class, 'obtenerEstadisticas']);
    Route::get('procesamiento-masivo/historial', [\App\Http\Controllers\ProcesamientoMasivoController::class, 'historial']);
    
    // Gestión de acopios rechazados
    Route::get('acopio-rechazos', [\App\Http\Controllers\AcopioRechazoController::class, 'index']);
    Route::get('acopio-rechazos/estadisticas', [\App\Http\Controllers\AcopioRechazoController::class, 'estadisticas']);
    Route::get('acopio-rechazos/{id}', [\App\Http\Controllers\AcopioRechazoController::class, 'show']);
    Route::get('acopio-rechazos/productor/{productorId}', [\App\Http\Controllers\AcopioRechazoController::class, 'rechazosPorProductor']);
    Route::patch('acopio-rechazos/{id}/notificar', [\App\Http\Controllers\AcopioRechazoController::class, 'marcarComoNotificado']);
    Route::patch('acopio-rechazos/{id}/devolver', [\App\Http\Controllers\AcopioRechazoController::class, 'marcarComoDevuelto']);
    Route::patch('acopio-rechazos/{id}/cancelar', [\App\Http\Controllers\AcopioRechazoController::class, 'cancelar']);
});
Route::get('acopiore2', [AcopioCosechaController::class, 'resumenMensual']);
Route::get('acopiore1', [AcopioCosechaController::class, 'resumenPorProducto']);
// Rutas públicas o fuera de autenticación para impresión de documentos y PDFs
Route::get('/documentos/{id}/imprimir', [DocumentoController::class, 'printDocument']);

// Rutas públicas para impresión de formularios de control
Route::get('/plagas/{cosechaId}/imprimir', [\App\Http\Controllers\PlagaController::class, 'printFormulario']);
Route::get('/limpiezas/{cosechaId}/imprimir', [\App\Http\Controllers\LimpiezaController::class, 'printFormulario']);
Route::get('/medicamentos/{cosechaId}/imprimir', [\App\Http\Controllers\MedicamentoController::class, 'printFormulario']);

Route::get('/ventas/{venta}/nota', [VentaController::class, 'notaPdf'])->name('ventas.nota');

//Route::get('/export', [MobileController::class, 'export']);
Route::get('mobile/export', [MobileController::class, 'export']);
Route::post('mobile/productores-sync', [MobileController::class, 'syncProductor']);
// PDF por productor
//Route::get('/productores/{productor}/certificaciones/print', [CertificacionController::class, 'printByProductor']);
