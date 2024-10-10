<?php

use App\Http\Controllers\Alumno\AlumnoController;
use App\Http\Controllers\Asistencia\AsistenciaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Curso\CursoController;
use App\Http\Controllers\Docente\DocenteController;
use App\Http\Controllers\Grado\GradoController;
use App\Http\Controllers\PadreFamilia\PadreFamiliaController;
use App\Http\Controllers\PagoCuotas\PagoController;
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
    Route::get('/lista-cursos', [CursoController::class, 'listaCursosCompletos']);
    Route::get('/curso', [CursoController::class, 'obtnerCursos']);

    Route::get('/alumnos/grado/{id_grado}', [AsistenciaController::class, 'obtnerAlumnoGrado']);

    Route::get('/alumno/{id}/familia', [AlumnoController::class, 'obtenerAlumnosConFamiliaPorGrado']);
    Route::get('/alumno/{id}', [AlumnoController::class, 'obtenerAlumnoPorIdConPadre']);
    Route::put('/alumno/{id}/editar', [AlumnoController::class, 'editarAlumnoYFamilia']);

    Route::post('/registrar-asistencia', [AsistenciaController::class, 'registrarAsistencia']);

    Route::get('/payment-pending', [PagoController::class, 'paymentPending']);



    Route::get('/lista-docente', [DocenteController::class, 'listaDocentes']);
    Route::put('/modificar-docente/{id}', [DocenteController::class, 'actualizarDatosDocente']);
    Route::get('/docente/{id}', [DocenteController::class, 'obtenerDocente']);
    Route::delete('/eliminar-docente/{id}', [DocenteController::class, 'eliminarDocente']);






    Route::get('/cuotas', [PagoController::class, 'obtenerCuotasAlumno']);
    Route::get('/cuotas/pagadas', [PagoController::class, 'obtenerCuotasPagadasAlumno']);


    Route::get('/cuotas-detalle/{id_grado}/lista', [PagoController::class, 'cuotaDetalles']);
    Route::post('/pagar/{cuotaNumero}', [PagoController::class, 'pagarCuota']);

    Route::post('/pagar-matricula', [PagoController::class, 'pagarMatricula']);
});


// Route::post('/pagar/{id}/{cuotaNumero}', [PagoController::class, 'pagarCuota']);
Route::post('/recibir-pago', [PagoController::class, 'notificacionPagos']);
Route::get('/payment-success/{id}/{cuotaEstado}', [PagoController::class, 'paymentSuccess']);
Route::get('/payment-failure', [PagoController::class, 'paymentFailure']);
Route::get('/payment-success-matricula/{id}', [PagoController::class, 'paymentSuccessMatricula']);
Route::post('/webhook/mercado-pago/matricula', [PagoController::class, 'notificacionPagosMatricula']);
Route::get('/payment-realizado-matricula/{id}', [PagoController::class, 'pagoRealizadoMatricula']);



Route::get('/validate-token', [AuthController::class, 'validateToken']);
