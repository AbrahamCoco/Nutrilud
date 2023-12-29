<?php

use App\Http\Controllers\Api\ArticulosController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Paciente\PacienteController;
use App\Http\Controllers\Api\Administrador\AdministradorController;
use App\Http\Controllers\Api\Nutriologo\NutriologoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    //Rutas publicas
    Route::get('/', [ArticulosController::class, 'index'])->name('index');
    Route::get('/articulo/{id}', [ArticulosController::class, 'show'])->name('show');

    //Rutas de registro y logeo
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('/upload/image', [AuthController::class, 'upload'])->name('upload');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
    Route::get('/auth/user/{id}', [AuthController::class, 'showDatos'])->name('showDatos');

    Route::post('/nutriologo/articulos', [ArticulosController::class, 'store'])->name('store');
    Route::get('/pacientes', [NutriologoController::class, 'show'])->name('show');
    Route::get('/paciente/{id}', [NutriologoController::class, 'showPaciente'])->name('showPaciente');
    Route::get('/consultadatos/{id}', [NutriologoController::class, 'showAllDatos'])->name('showAllDatos');
    Route::post('/insertardatos/{id}', [NutriologoController::class, 'insertarDatos'])->name('insertarDatos');

    //Rutas privadas
    Route::middleware('auth:sanctum')->group(function () {
        //Rutas de logeo
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

        //Rutas de paciente
        //Route::apiResource('/paciente', [PacienteController::class]);

        //Rutas de nutriologo
        // Route::get('/nutriologo/datosPerfil/{id}', [NutriologoController::class, 'showDatos'])->name('showDatos');

        //Rutas de administrador
        Route::apiResource('/administrador', AdministradorController::class);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
