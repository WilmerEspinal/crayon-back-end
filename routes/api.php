<?php

use App\Http\Controllers\Alumno\AlumnoController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Docente\DocenteController;
use App\Http\Controllers\Grado\GradoController;
use App\Http\Controllers\PadreFamilia\PadreFamiliaController;
use App\Http\Controllers\Persona\PersonaController;
use App\Http\Controllers\ReniecConsultas\ReniecConsultasController;
use App\Http\Controllers\Roles\RolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/* Rutas publicas  */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::get('/rol', [RolController::class, 'obtnerRoles']);
Route::get('/consulta-dni/{dni}', [ReniecConsultasController::class, 'consultasDNI']);

Route::middleware(['jwt-verify'])->group(function () {
    Route::get('/users', [AuthController::class, 'obtenerUsuario']);
    Route::post('/datos-estudiante', [PersonaController::class, 'registarDatosPersona']);
    Route::post('/registrar-docente', [DocenteController::class, 'registrarDatosDocente']);


    Route::post('/logout', [AuthController::class, 'serrarSesion']);
    Route::get('/lista-grado', [GradoController::class, 'obtnerGrado']);
});

Route::get('/validate-token', [AuthController::class, 'validateToken']);
