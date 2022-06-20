<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
use App\Http\Controllers\ContrladoresDocentes\ControladorTutorFCT;
use App\Http\Controllers\ControladorAlumnos\ControladorAlumno;
use App\Http\Controllers\ControladorEmpresas\ControladorResponCentro;
use App\Http\Controllers\ControladorGenerico;
use App\Http\Controllers\GastosProfesor;
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

    /********************Gestión de Notificaciones**************************/
    Route::post('/getNotificaciones', [ControladorGenerico::class, 'getNotificaciones']);
    Route::post('/generarNotificaciones', [ControladorGenerico::class, 'generarNotificaciones']);
    Route::post('/getNotificacionesHeader', [ControladorGenerico::class, 'getNotificacionesHeader']);
    Route::post('/countNotificaciones', [ControladorGenerico::class, 'countNotificaciones']);
    Route::put('/cambiarLeido', [ControladorGenerico::class, 'cambiarLeido']);
    /***********************************************************************/

    /****************Obtener familias profesionales y ciclos****************/
    Route::get('/familias_profesionales', [ControladorGenerico::class, 'getFamiliasProfesionales']);
    Route::get('/ciclos/{familia?}', [ControladorGenerico::class, 'getCiclos']);
    /***********************************************************************/

    Route::post('/descargar_anexo_ruta', [ControladorGenerico::class, 'descargarAnexoRuta']);
    Route::get('check_duplicado/{elemento}.{campo}={valor}', [ControladorGenerico::class, 'checkDuplicate']);

    /***********************************************************************/
    /*********************Cuestionarios Tutores Empresa*********************/
    Route::get('/obtenerCuestionariosFCT/{dni_tutor}', [ControladorJefatura::class, 'obtenerCuestionariosTutorEmpresaAlumnos']);
    Route::get('/verificarCuestionarioRespondidoFCT/{id_usuario}', [ControladorJefatura::class, 'verificarCuestionarioRespondido']);
    Route::get('/obtenerCuestionarioFCT/{destinatario}/{codigo_centro}', [ControladorJefatura::class, 'obtenerCuestionario']);
    Route::post('/crearCuestionarioRespondidoFCT', [ControladorJefatura::class, 'crearCuestionarioRespondido']);
    Route::get('/obtenerCursosAcademicos', [ControladorJefatura::class, 'obtenerCursosAcademicos']);
    Route::get('/obtenerMediasCuestionariosRespondidosFCT', [ControladorJefatura::class, 'obtenerMediasCuestionariosRespondidos']);
    Route::get('/listarCuestionariosRespondidosFCT', [ControladorJefatura::class, 'listarCuestionariosRespondidos']);
    Route::get('/descargarCuestionarioFCT/{id_cuestionario}', [ControladorJefatura::class, 'descargarCuestionario']);
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
    Route::get('solicitar_empresa/id={id}', [ControladorTutorFCT::class, 'getEmpresaID']);
    Route::get('solicitar_empresa/cif={cif}', [ControladorTutorFCT::class, 'getEmpresaCIF']);
    Route::get('solicitar_representante/id={id}', [ControladorTutorFCT::class, 'getRepresentanteLegalResponse']);
    Route::put('update_empresa', [ControladorTutorFCT::class, 'updateEmpresa']);
    Route::put('update_trabajador', [ControladorTutorFCT::class, 'updateTrabajador']);
    Route::delete('delete_empresa/id={id}', [ControladorTutorFCT::class, 'deleteEmpresa']);
    Route::post('addDatosEmpresa', [ControladorTutorFCT::class, 'addDatosEmpresa']);
    //----Gestión del convenio / acuerdo
    Route::get('solicitar_centro_estudios/convenio={cod_convenio}', [ControladorTutorFCT::class, 'getCentroEstudiosFromConvenioJSON']);
    Route::post('descargarAnexo0', [ControladorTutorFCT::class, 'descargarAnexo0']);
    Route::post('add_convenio', [ControladorTutorFCT::class, 'addConvenio']);
    Route::put('editar_convenio', [ControladorTutorFCT::class, 'updateConvenio']);
    Route::delete('eliminar_convenio/cod={cod}', [ControladorTutorFCT::class, 'deleteConvenio']);
    /***********************************************************************/

    /******************************CRUD ANEXOS******************************/
    Route::post('/relleno', [ControladorTutorFCT::class, 'rellenarAnexo1']);
    Route::get('/listarAnexos/{dni}/{habilitado}', [ControladorTutorFCT::class, 'verAnexos']);
    Route::get('/listarGrupos/{dni}', [ControladorTutorFCT::class, 'verGrupos']);
    Route::delete('/eliminarAnexo/{dni_tutor}/{cod_anexo}', [ControladorTutorFCT::class, 'eliminarAnexo']);
    Route::post('/deshabilitarAnexo', [ControladorTutorFCT::class, 'deshabilitarAnexo']);
    Route::post('/habilitarAnexo', [ControladorTutorFCT::class, 'habilitarAnexo']);
    /***********************************************************************/

    /******************************CRUD ALUMNOS******************************/
    Route::get('/listarAlumnos/{dni_logueado}', [ControladorJefatura::class, 'listarAlumnos']);
    Route::get('/verAlumno/{dni_alumno}', [ControladorJefatura::class, 'verAlumno']);
    Route::post('/addAlumno', [ControladorJefatura::class, 'addAlumno']);
    Route::put('/modificarAlumno', [ControladorJefatura::class, 'modificarAlumno']);
    Route::delete('/eliminarAlumno/{dni_alumno}', [ControladorJefatura::class, 'eliminarAlumno']);
    Route::get('/listarGrupos', [ControladorJefatura::class, 'listarGrupos']);
    Route::get('/descargarCurriculum/{dni}', [ControladorJefatura::class, 'descargarCurriculum']);
    Route::get('/generarAnexoFEM05/{dni_alumno}', [ControladorJefatura::class, 'generarAnexoFEM05']);
    /************************************************************************/

    /***********************ASIGNACIÓN ALUMNO-EMPRESA***********************/
    Route::get('/solicitarAlumnosSinEmpresa/{dni}', [ControladorTutorFCT::class, 'solicitarAlumnosSinEmpresa']);
    Route::get('/solicitarEmpresasConAlumnos/{dni}', [ControladorTutorFCT::class, 'solicitarEmpresasConAlumnos']);
    Route::get('/solicitarNombreCiclo/{dni}', [ControladorTutorFCT::class, 'solicitarNombreCiclo']);
    Route::post('/actualizarEmpresaAsignadaAlumno', [ControladorTutorFCT::class, 'actualizarEmpresaAsignadaAlumno']);
    /***********************************************************************/
    /*******************************ANEXO II Y IV*******************************/
    Route::post('/rellenarAnexoIIYIV', [ControladorTutorFCT::class, 'rellenarAnexoIIYIV']);
    Route::get('/solicitarAnexosProgramaFormativo/{dni_tutor}', [ControladorTutorFCT::class, 'listarAnexosIIYIV']);
    /**********************************************************************/
    //----Listar anexos
    Route::get('/solicitarAnexosFct/{dni_tutor}', [ControladorTutorFCT::class, 'listarAnexos1']);
    /***********************CRUD GASTOS ALUMNOS TUTOR***********************/
    Route::get('/gestionGastosProfesor', [ControladorTutorFCT::class, 'gestionGastosProfesor']);
    Route::delete('/eliminarAlumnoDeGastos/{dni_alumno}', [ControladorTutorFCT::class, 'eliminarAlumnoDeGastos']);
    Route::post('/nuevoAlumnoGestionGastos', [ControladorTutorFCT::class, 'nuevoAlumnoGestionGastos']);
    Route::get('/descargarAnexoVI', [ControladorTutorFCT::class, 'descargarAnexoVI']);
    Route::post('/confirmar_trayectos', [ControladorTutorFCT::class, 'confirmarTrayectos']);
    Route::post('/firmar_anexo_vii', [ControladorTutorFCT::class, 'subirAnexoVII']);
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
    /*******************************SUBIDA CSV*******************************/
    Route::post('recibirCSV', [ControladorJefatura::class, 'recibirCSV']);
    /************************************************************************/
});

Route::group(['prefix' => 'jefatura', 'middleware' => ['Cors', 'auth:api', 'jefatura']], function () {
    /*************************CRUD CUESTIONARIOS***************************/
    Route::post('/crearCuestionario', [ControladorJefatura::class, 'crearCuestionario']);
    Route::post('/editarCuestionario', [ControladorJefatura::class, 'editarCuestionario']);
    Route::get('/obtenerCuestionarioEdicion/{id}', [ControladorJefatura::class, 'obtenerCuestionarioEdicion']);
    Route::get('/obtenerCuestionario/{destinatario}/{codigo_centro}', [ControladorJefatura::class, 'obtenerCuestionario']);
    Route::post('/crearCuestionarioRespondido', [ControladorJefatura::class, 'crearCuestionarioRespondido']);
    Route::get('/listarCuestionarios/{codigo_centro}', [ControladorJefatura::class, 'listarCuestionarios']);
    Route::get('/verificarCuestionarioRespondido/{id_usuario}', [ControladorJefatura::class, 'verificarCuestionarioRespondido']);
    Route::delete('/eliminarCuestionario/{id}', [ControladorJefatura::class, 'eliminarCuestionario']);
    Route::get('/obtenerCuestionariosFCT/{dni_tutor}', [ControladorJefatura::class, 'obtenerCuestionariosTutorEmpresaAlumnos']);
    Route::post('/activarCuestionario/{id_cuestionario}/{destinatario}/{codigo_centro}', [ControladorJefatura::class, 'activarCuestionario']);
    Route::post('/desactivarCuestionario/{id_cuestionario}', [ControladorJefatura::class, 'desactivarCuestionario']);
    // Route::get('/obtenerCursosAcademicos', [ControladorJefatura::class, 'obtenerCursosAcademicos']);
    Route::get('/obtenerMediasCuestionariosRespondidos', [ControladorJefatura::class, 'obtenerMediasCuestionariosRespondidos']);
    Route::get('/listarCuestionariosRespondidos', [ControladorJefatura::class, 'listarCuestionariosRespondidos']);
    Route::get('/descargarCuestionario/{id_cuestionario}', [ControladorJefatura::class, 'descargarCuestionario']);
    /**********************************************************************/
});


/*
|--------------------------------------------------------------------------
| Rutas a las que pueden acceder tutor y alumno
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'alumno_tutor']], function () {
    /*******************************ANEXO XV*******************************/
    Route::post('/rellenarAnexoXV', [ControladorAlumno::class, 'rellenarAnexoXV']);
    /**********************************************************************/
});

Route::group(['middleware' => ['Cors', 'auth:api', 'seguimiento']], function () {
    /************************SEGUIMIENTO - ANEXO III************************/
    Route::any('/addJornada', [ControladorAlumno::class, 'addJornada']);
    Route::any('/devolverDatosAlumno', [ControladorAlumno::class, 'devolverDatosAlumno']);
    Route::any('/gestionarDepartamento', [ControladorAlumno::class, 'gestionarDepartamento']);
    Route::put('/addDepartamento', [ControladorAlumno::class, 'addDepartamento']);
    Route::post('/sumatorioHorasTotales', [ControladorAlumno::class, 'sumatorioHorasTotales']);
    Route::post('/devolverJornadas', [ControladorAlumno::class, 'devolverJornadas']);
    Route::post('/devolverSemanas', [ControladorAlumno::class, 'devolverSemanas']);
    Route::post('/updateJornada', [ControladorAlumno::class, 'updateJornada']);
    Route::post('/recogerJornadas', [ControladorAlumno::class, 'recogerJornadas']);
    Route::post('/generarAnexo3', [ControladorAlumno::class, 'generarAnexo3']);
    Route::get('/listaAnexosAlumno/{dni}', [ControladorAlumno::class, 'listaAnexosAlumno']);
    Route::post('/descargarTodoAlumnos', [ControladorAlumno::class, 'descargarTodoAlumnos']);
    //----Gestión del tutor de la empresa
    Route::post('recogerTutorEmpresa', [ControladorAlumno::class, 'recogerTutorEmpresa']);
    Route::put('actualizarTutorEmpresa', [ControladorAlumno::class, 'actualizarTutorEmpresa']);
    //----AnexoXV
    Route::post('/rellenarAnexoXV', [ControladorAlumno::class, 'rellenarAnexoXV']);
    //----AnexoV
    Route::post('/confirmar_gastos', [ControladorAlumno::class, 'confirmarGastos']);
    Route::post('/firmar_anexo_v', [ControladorAlumno::class, 'subirAnexoV']);
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
    /**********************************************************************/
    //Gestión de hoja de seguimiento:
    Route::post('/generarAnexo3', [ControladorAlumno::class, 'generarAnexo3']);
    Route::post('/descargarAnexo3', [ControladorAlumno::class, 'descargarAnexo3']);
    Route::post('/hayDocumento', [ControladorAlumno::class, 'hayDocumento']);
    Route::post('/subirAnexo3', [ControladorAlumno::class, 'subirAnexo3']);
    /**********************************************************************/

    Route::post('/getAlumnosAsociados', [ControladorAlumno::class, 'getAlumnosAsociados']);

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

     /******************************CUESTIONARIOS**************************/
    Route::get('/verificarCuestionarioRespondido/{id_usuario}', [ControladorJefatura::class, 'verificarCuestionarioRespondido']);
    Route::get('/obtenerCuestionario/{destinatario}/{codigo_centro}', [ControladorJefatura::class, 'obtenerCuestionario']);
    Route::post('/crearCuestionarioRespondido', [ControladorJefatura::class, 'crearCuestionarioRespondido']);
    /**********************************************************************/


});

/*
|--------------------------------------------------------------------------
| Rutas a las que pueden acceder sólo los alumnos
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['Cors', 'auth:api', 'alumno']], function () {
});
