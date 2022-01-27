<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class,'solicitarAlumnosSinEmpresa']);
Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class,'solicitarEmpresasConAlumnos']);
Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class,'solicitarNombreCiclo']);
Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class,'actualizarEmpresaAsignadaAlumno']);

Route::any('/relleno', [ControladorTutorFCT::class, 'rellenarAnexo1']);
