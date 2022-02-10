<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
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

Route::group(['middleware' => ['Cors']], function () {
    Route::post('addDatosEmpresa', [ControladorTutorFCT::class, 'addDatosEmpresa']);
    Route::post('addConvenio', [ControladorTutorFCT::class, 'addConvenio']);
    Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class, 'solicitarAlumnosSinEmpresa']);
    Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class, 'solicitarEmpresasConAlumnos']);
    Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class, 'solicitarNombreCiclo']);
    Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class, 'actualizarEmpresaAsignadaAlumno']);
    Route::any('/relleno', [ControladorTutorFCT::class, 'rellenarAnexo1']);
    Route::any('/addJornada',[ControladorAlumno::class,'addJornada']);
    Route::any('/devolverDatosAlumno',[ControladorAlumno::class,'devolverDatosAlumno']);
    Route::any('/gestionarDepartamento',[ControladorAlumno::class,'gestionarDepartamento']);
    Route::put('/addDepartamento',[ControladorAlumno::class,'addDepartamento']);
    Route::post('/sumatorioHorasTotales', [ControladorAlumno::class, 'sumatorioHorasTotales']);
    Route::post('/devolverJornadas', [ControladorAlumno::class, 'devolverJornadas']);
    Route::post('/updateJornada', [ControladorAlumno::class, 'updateJornada']);
    Route::post('/recogerJornadas', [ControladorAlumno::class, 'recogerJornadas']);
    Route::post('/generarAnexo3', [ControladorAlumno::class, 'generarAnexo3']);


});

Route::group(['prefix' => 'jefatura', 'middleware' => ['Cors']], function () {
    //Por si se me olvida a posteriorri: está puesto como un get para pruebas,
    //por favor, cambiar a post
    Route::post('recibirCSV', [ControladorJefatura::class, 'recibirCSV']);
});

