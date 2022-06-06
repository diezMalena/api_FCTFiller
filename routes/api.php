<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
use App\Http\Controllers\ControladorEmpresas\ControladorResponCentro;
use App\Http\Controllers\ControladorGenerico;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Rutas de autenticación
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['Cors']], function () {
    Route::post('/login', [ControladorGenerico::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas genéricas
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors']], function () {
    /*********************Obtener provincias y ciudades*********************/
    Route::get('/listarProvincias', [ControladorGenerico::class, 'listarProvincias']);
    Route::get('/listarCiudades/{provincia}', [ControladorGenerico::class, 'listarCiudades']);
    /***********************************************************************/
});

/*
|--------------------------------------------------------------------------
| Rutas para los perfiles del tutor y el docente genérico
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'profesor']], function () {
    /*****************************CRUD EMPRESAS*****************************/
    Route::get('solicitar_empresas/profesor={dniProfesor}', [ControladorTutorFCT::class, 'getEmpresasFromProfesor']);
    Route::get('solicitar_representante/id={id}', [ControladorTutorFCT::class, 'getRepresentanteLegalResponse']);
    Route::put('update_empresa', [ControladorTutorFCT::class, 'updateEmpresa']);
    Route::put('update_trabajador', [ControladorTutorFCT::class, 'updateTrabajador']);
    Route::delete('delete_empresa/id={id}', [ControladorTutorFCT::class, 'deleteEmpresa']);
    Route::post('addDatosEmpresa', [ControladorTutorFCT::class, 'addDatosEmpresa']);
    Route::post('addConvenio', [ControladorTutorFCT::class, 'addConvenio']);
    Route::post('descargarAnexo0', [ControladorTutorFCT::class, 'descargarAnexo0']);
    /***********************************************************************/

    /******************************CRUD ANEXOS******************************/
    Route::post('/relleno', [ControladorTutorFCT::class, 'rellenarAnexo1']);
    Route::get('/listarAnexos/{dni}/{habilitado}', [ControladorTutorFCT::class, 'verAnexos']);
    Route::get('/listarGrupos/{dni}', [ControladorTutorFCT::class, 'verGrupos']);
    Route::delete('/eliminarAnexo/{dni_tutor}/{cod_anexo}', [ControladorTutorFCT::class, 'eliminarAnexo']);
    Route::post('/deshabilitarAnexo', [ControladorTutorFCT::class, 'deshabilitarAnexo']);
    Route::post('/habilitarAnexo', [ControladorTutorFCT::class, 'habilitarAnexo']);
     /***********************************************************************/
});

Route::group(['middleware' => ['Cors', 'auth:api', 'tutor']], function () {
    /***********************ASIGNACIÓN ALUMNO-EMPRESA***********************/
    Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class, 'solicitarAlumnosSinEmpresa']);
    Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class, 'solicitarEmpresasConAlumnos']);
    Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class, 'solicitarNombreCiclo']);
    Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class, 'actualizarEmpresaAsignadaAlumno']);
    /***********************************************************************/
    /*******************************ANEXO II Y IV*******************************/
    Route::post('/rellenarAnexoIIYIV', [ControladorTutorFCT::class, 'rellenarAnexoIIYIV']);
    /**********************************************************************/
    //----Listar anexos
    Route::get('/solicitarAnexosFct/{dni_tutor}', [ControladorTutorFCT::class, 'listarAnexos1']);
    Route::get('/solicitarAnexosProgramaFormativo/{dni_tutor}', [ControladorTutorFCT::class, 'listarAnexosIIYIV']);

});

/*
|--------------------------------------------------------------------------
| Rutas para los perfiles de jefatura
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'jefatura']], function () {
    /****************************CRUD PROFESORES****************************/
    Route::get('/listarProfesores/{dni_profesor}', [ControladorJefatura::class, 'verProfesores']);
    Route::get('/listarProfesor/{dni_profesor}', [ControladorJefatura::class, 'verProfesor']);
    Route::get('/listarProfesorEditar/{dni_profesor}', [ControladorJefatura::class, 'verProfesorEditar']);
    Route::delete('/eliminarProfesor/{dni_profesor}', [ControladorJefatura::class, 'eliminarProfesor']);
    Route::post('/modificarProfesor', [ControladorJefatura::class, 'modificarProfesor']);
    Route::post('/addProfesor', [ControladorJefatura::class, 'addProfesor']);
    /***********************************************************************/
});

Route::group(['prefix' => 'jefatura', 'middleware' => ['Cors', 'auth:api', 'jefatura']], function () {
    /*******************************SUBIDA CSV*******************************/
    Route::post('recibirCSV', [ControladorJefatura::class, 'recibirCSV']);
    /************************************************************************/

    /******************************CRUD ALUMNOS******************************/
    Route::get('/listarAlumnos/{dni_logueado}', [ControladorJefatura::class, 'listarAlumnos']);
    Route::get('/verAlumno/{dni_alumno}', [ControladorJefatura::class, 'verAlumno']);
    Route::post('/addAlumno', [ControladorJefatura::class, 'addAlumno']);
    Route::put('/modificarAlumno', [ControladorJefatura::class, 'modificarAlumno']);
    Route::delete('/eliminarAlumno/{dni_alumno}', [ControladorJefatura::class, 'eliminarAlumno']);
    Route::get('/listarGrupos', [ControladorJefatura::class, 'listarGrupos']);
    Route::get('/descargarFotoPerfil/{dni}/{guid}', [ControladorJefatura::class, 'descargarFotoPerfil']);
    Route::get('/descargarCurriculum/{dni}/{guid}', [ControladorJefatura::class, 'descargarCurriculum']);
    /************************************************************************/
});

/*
|--------------------------------------------------------------------------
| Rutas a las que pueden acceder tutor y alumno
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'alumno_tutor']], function () {
    /************************SEGUIMIENTO - ANEXO III************************/
    Route::any('/addJornada', [ControladorAlumno::class, 'addJornada']);
    Route::any('/devolverDatosAlumno', [ControladorAlumno::class, 'devolverDatosAlumno']);
    Route::any('/gestionarDepartamento', [ControladorAlumno::class, 'gestionarDepartamento']);
    Route::put('/addDepartamento', [ControladorAlumno::class, 'addDepartamento']);
    Route::post('/sumatorioHorasTotales', [ControladorAlumno::class, 'sumatorioHorasTotales']);
    Route::post('/devolverJornadas', [ControladorAlumno::class, 'devolverJornadas']);
    Route::post('/updateJornada', [ControladorAlumno::class, 'updateJornada']);
    Route::post('/recogerJornadas', [ControladorAlumno::class, 'recogerJornadas']);
    Route::post('/generarAnexo3', [ControladorAlumno::class, 'generarAnexo3']);
    Route::get('/listaAnexosAlumno/{dni}', [ControladorAlumno::class, 'listaAnexosAlumno']);
    Route::post('/descargarTodoAlumnos', [ControladorAlumno::class, 'descargarTodoAlumnos']);
    //----Gestión del tutor de la empresa
    Route::post('recogerTutorEmpresa', [ControladorAlumno::class, 'recogerTutorEmpresa']);
    Route::get('getTutoresResponsables/id={id_empresa}', [ControladorAlumno::class, 'getTutoresResponsables']);
    Route::put('actualizarTutorEmpresa', [ControladorAlumno::class, 'actualizarTutorEmpresa']);
    //----AnexoXV
    Route::post('/rellenarAnexoXV', [ControladorAlumno::class, 'rellenarAnexoXV']);
});

/*
|--------------------------------------------------------------------------
| Rutas a las que pueden acceder cualquier docente y alumno
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'alumno_profesor']], function () {

    //----Crud-Anexos
    Route::post('/descargarAnexo', [ControladorTutorFCT::class, 'descargarAnexo']);
    Route::post('/descargarTodo', [ControladorTutorFCT::class, 'descargarTodo']);
    //----Subir Anexo Especifico
    Route::post('/subirAnexoEspecifico', [ControladorTutorFCT::class, 'subirAnexoEspecifico']);
});

Route::middleware('auth:api')->any('prueba', [ControladorGenerico::class, 'prueba']);
