<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
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
    Route::get('listarProvincias', [ControladorGenerico::class, 'listarProvincias']);
    Route::get('listarCiudades/{provincia}', [ControladorGenerico::class, 'listarCiudades']);
    /***********************************************************************/
    /****************************CRUD Alumnos*******************************/
    Route::get('descargarFotoPerfil/{dni}/{guid}', [ControladorJefatura::class, 'descargarFotoPerfil']);
    /***********************************************************************/
    /**************** CRUD Factura Transporte y Manutencion ****************/
    Route::get('descargarImagenTicketTransporte/{id}/{guid}', [ControladorAlumno::class, 'descargarImagenTicketTransporte']);
    Route::get('descargarImagenTicketManutencion/{id}/{guid}', [ControladorAlumno::class, 'descargarImagenTicketManutencion']);
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
    Route::get('/listarAnexos/{dni}', [ControladorTutorFCT::class, 'verAnexos']);
    Route::get('/listarGrupos/{dni}', [ControladorTutorFCT::class, 'verGrupos']);
    Route::post('/descargarAnexo', [ControladorTutorFCT::class, 'descargarAnexo']);
    Route::post('/descargarTodo', [ControladorTutorFCT::class, 'descargarTodo']);
    Route::delete('/eliminarAnexo/{dni_tutor}/{cod_anexo}', [ControladorTutorFCT::class, 'eliminarAnexo']);
    Route::post('/deshabilitarAnexo', [ControladorTutorFCT::class, 'deshabilitarAnexo']);
    Route::post('/habilitarAnexo', [ControladorTutorFCT::class, 'habilitarAnexo']);
    Route::get('/listarHistorial/{dni}', [ControladorTutorFCT::class, 'verHistorialAnexos']);
    /***********************************************************************/
});

Route::group(['middleware' => ['Cors', 'auth:api', 'tutor']], function () {
    /***********************ASIGNACIÓN ALUMNO-EMPRESA***********************/
    Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class, 'solicitarAlumnosSinEmpresa']);
    Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class, 'solicitarEmpresasConAlumnos']);
    Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class, 'solicitarNombreCiclo']);
    Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class, 'actualizarEmpresaAsignadaAlumno']);
    /***********************************************************************/
    /***********************CRUD GASTOS ALUMNOS TUTOR***********************/
    Route::get('/gestionGastosProfesor', [ControladorTutorFCT::class, 'gestionGastosProfesor']);
    Route::delete('/eliminarAlumnoDeGastos/{dni_alumno}', [ControladorTutorFCT::class, 'eliminarAlumnoDeGastos']);
    /***********************************************************************/

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
    Route::get('/descargarCurriculum/{dni}', [ControladorJefatura::class, 'descargarCurriculum']);
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
    //----Gestión del tutor de la empresa
    Route::post('recogerTutorEmpresa', [ControladorAlumno::class, 'recogerTutorEmpresa']);
    Route::get('getTutoresResponsables/id={id_empresa}', [ControladorAlumno::class, 'getTutoresResponsables']);
    Route::put('actualizarTutorEmpresa', [ControladorAlumno::class, 'actualizarTutorEmpresa']);
    /**********************************************************************/

    /*******************************ANEXO XV*******************************/
    Route::post('/rellenarAnexoXV', [ControladorAlumno::class, 'rellenarAnexoXV']);
    /**********************************************************************/

    /******************************ANEXO VI********************************/
    Route::get('/gestionGastosAlumno/{dni_alumno}', [ControladorAlumno::class, 'gestionGastosAlumno']);
    Route::put('/actualizarDatosGastoAlumno', [ControladorAlumno::class, 'actualizarDatosGastoAlumno']);
    Route::put('/actualizarDiasVehiculoPrivado', [ControladorAlumno::class, 'actualizarDiasVehiculoPrivado']);
    Route::put('/actualizarFacturaTransporte', [ControladorAlumno::class, 'actualizarFacturaTransporte']);
    Route::post('/nuevaFacturaTransporte', [ControladorAlumno::class, 'nuevaFacturaTransporte']);
    Route::delete('/eliminarFacturaTransporte/{id}', [ControladorAlumno::class, 'eliminarFacturaTransporte']);
    Route::put('/actualizarFacturaManutencion', [ControladorAlumno::class, 'actualizarFacturaManutencion']);
    Route::post('/nuevaFacturaManutencion', [ControladorAlumno::class, 'nuevaFacturaManutencion']);
    Route::delete('/eliminarFacturaManutencion/{id}', [ControladorAlumno::class, 'eliminarFacturaManutencion']);
    /**********************************************************************/
});


Route::middleware('auth:api')->any('prueba', [ControladorGenerico::class, 'prueba']);
