<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ReniecConsultas\ReniecConsultasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/* Rutas publicas  */

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/change-password', [AuthController::class, 'changePassword']);

Route::get('/consulta-dni/{dni}', [ReniecConsultasController::class, 'consultasDNI']);
Route::middleware(['jwt-verify'])->group(function () {
    Route::get('/users', [AuthController::class, 'obtenerUsuario']);
});

Route::get('/validate-token', [AuthController::class, 'validateToken']);