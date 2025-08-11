<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Asegúrate de importar el controlador con el nombre exacto:
use App\Http\Controllers\Api\GradoTituloController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\DocenteContratadoController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\RegimenController;
use App\Http\Controllers\Api\FacultadController;


use App\Http\Controllers\Api\DocenteNombradoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

//
// 1) Rutas protegidas por sanctum (grupo “auth:sanctum”):
//
Route::middleware('auth:sanctum')->group(function () {
    // Ejemplo de ruta que devuelve el usuario autenticado:
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rutas de Grados y Títulos (métodos index, store, show, update, destroy)
    // Fíjate que acá usamos el controlador con EXACTO nombre de la clase:
    Route::get      ('/grados-titulos',        [GradoTituloController::class, 'index']);
    Route::post     ('/grados-titulos',        [GradoTituloController::class, 'store']);
    Route::get      ('/grados-titulos/{id}',   [GradoTituloController::class, 'show']);
    Route::put      ('/grados-titulos/{id}',   [GradoTituloController::class, 'update']);
    Route::delete   ('/grados-titulos/{id}',   [GradoTituloController::class, 'destroy']);

    // Rutas de Docentes
    Route::get      ('/docentes',             [DocenteController::class, 'index']);
    Route::post     ('/docentes/create',      [DocenteController::class, 'create']);
    Route::get      ('/docentes/{id}',        [DocenteController::class, 'show']);
    // Si quisieras edit/update de docente, agregarías aquí la ruta PUT /docentes/{id}
});

    // Opción A  (mínimas 3 rutas: index, store, show)
    Route::apiResource('docentes-nombrados', DocenteNombradoController::class)
        ->only(['index', 'store', 'show']);
// routes/api.php
Route::put  ('/docentes-nombrados/{docente}', [DocenteNombradoController::class, 'update']);
Route::patch('/docentes-nombrados/{docente}', [DocenteNombradoController::class, 'update']);

//
// 2) Rutas de autenticación sin middleware (login, register, logout separado de grupo anterior)
//
Route::post('login',    [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Logout debe estar protegido por sanctum, así que lo metemos en middleware:
Route::post('logout',   [AuthController::class, 'logout'])
     ->middleware('auth:sanctum');

//
// 3) Ruta de prueba pública (sin autenticación)
//
Route::get('/test', [TestController::class, 'index']);


Route::apiResource('docentes-contratados', DocenteContratadoController::class);
Route::post('docentes-contratados/import', [DocenteContratadoController::class, 'import']);
// routes/api.php  (grupo protegido por sanctum, como los demás)
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/regimenes',  [RegimenController::class,  'index']);
Route::get('/facultades', [FacultadController::class, 'index']);


