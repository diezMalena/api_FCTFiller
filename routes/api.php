<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorGenericoDocente;
use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
use App\Http\Controllers\ControladorGenerico;
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

    //CRUD Empresas
    Route::get('solicitar_empresas/profesor={dniProfesor}', [ControladorTutorFCT::class, 'getEmpresasFromProfesor']);
    Route::get('solicitar_representante/id={id}', [ControladorTutorFCT::class, 'getRepresentanteLegalResponse']);
    Route::put('update_empresa', [ControladorTutorFCT::class, 'updateEmpresa']);
    Route::put('update_trabajador', [ControladorTutorFCT::class, 'updateTrabajador']);
    Route::delete('delete_empresa/id={id}', [ControladorTutorFCT::class, 'deleteEmpresa']);

    Route::post('addDatosEmpresa', [ControladorTutorFCT::class, 'addDatosEmpresa']);
    Route::post('addConvenio', [ControladorTutorFCT::class, 'addConvenio']);

    //Gestión de alumnos asignados a una empresa.
    Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class, 'solicitarAlumnosSinEmpresa']);
    Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class, 'solicitarEmpresasConAlumnos']);
    Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class, 'solicitarNombreCiclo']);
    Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class, 'actualizarEmpresaAsignadaAlumno']);

    //Login
    Route::post('/login', [ControladorGenerico::class, 'login']);


    Route::any('/addJornada', [ControladorAlumno::class, 'addJornada']);
    Route::any('/devolverDatosAlumno', [ControladorAlumno::class, 'devolverDatosAlumno']);
    Route::any('/gestionarDepartamento', [ControladorAlumno::class, 'gestionarDepartamento']);
    Route::put('/addDepartamento', [ControladorAlumno::class, 'addDepartamento']);
    Route::post('/sumatorioHorasTotales', [ControladorAlumno::class, 'sumatorioHorasTotales']);
    Route::post('/devolverJornadas', [ControladorAlumno::class, 'devolverJornadas']);
    Route::post('/updateJornada', [ControladorAlumno::class, 'updateJornada']);
    Route::post('/recogerJornadas', [ControladorAlumno::class, 'recogerJornadas']);
    Route::post('/generarAnexo3', [ControladorAlumno::class, 'generarAnexo3']);


    Route::post('descargarAnexo0', [ControladorTutorFCT::class, 'descargarAnexo0']);
});



//Crud Anexos
Route::post('/relleno', [ControladorTutorFCT::class, 'rellenarAnexo1']);
Route::get('/listarAnexos/{dni}', [ControladorTutorFCT::class, 'verAnexos']);
Route::get('/listarGrupos/{dni}', [ControladorTutorFCT::class, 'verGrupos']);
Route::post('/descargarAnexo', [ControladorTutorFCT::class, 'descargarAnexo']);
Route::post('/descargarTodo', [ControladorTutorFCT::class, 'descargarTodo']);
Route::delete('/eliminarAnexo/{dni_tutor}/{cod_anexo}', [ControladorTutorFCT::class, 'eliminarAnexo']);


//Crud Profesores
Route::get('/listarProfesores/{dni_profesor}', [ControladorJefatura::class, 'verProfesores']);
Route::get('/listarProfesor/{dni_profesor}', [ControladorJefatura::class, 'verProfesor']);
Route::get('/listarProfesorEditar/{dni_profesor}', [ControladorJefatura::class, 'verProfesorEditar']);
Route::delete('/eliminarProfesor/{dni_profesor}', [ControladorJefatura::class, 'eliminarProfesor']);
Route::post('/modificarProfesor', [ControladorJefatura::class, 'modificarProfesor']);
Route::post('/addProfesor', [ControladorJefatura::class, 'addProfesor']);

//Obtener provincias y ciudades
Route::get('/listarProvincias', [ControladorGenerico::class, 'listarProvincias']);
Route::get('/listarCiudades/{provincia}', [ControladorGenerico::class, 'listarCiudades']);

Route::group(['prefix' => 'jefatura', 'middleware' => ['Cors']], function () {
    //Por si se me olvida a posteriorri: está puesto como un get para pruebas,
    //por favor, cambiar a post
    Route::post('recibirCSV', [ControladorJefatura::class, 'recibirCSV']);

    //CRUD Alumnos
    Route::get('/listarAlumnos/{dni_logueado}', [ControladorJefatura::class, 'listarAlumnos']);
    Route::get('/verAlumno/{dni_alumno}', [ControladorJefatura::class, 'verAlumno']);
    Route::post('/addAlumno', [ControladorJefatura::class, 'addAlumno']);
    Route::put('/modificarAlumno', [ControladorJefatura::class, 'modificarAlumno']);
    Route::delete('/eliminarAlumno/{dni_alumno}', [ControladorJefatura::class, 'eliminarAlumno']);


});
