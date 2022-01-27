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


Route::group(['middleware' => ['Cors']], function(){
    Route::post('addDatosEmpresa',[ControladorTutorFCT::class,'addDatosEmpresa']);
    Route::post('addConvenio',[ControladorTutorFCT::class,'addConvenio']);
    Route::post('generarConvenio', [ControladorTutorFCT::class, 'generarAnexo0']);
    //http://localhost:8000/api/docentes/hacerConvenio/tutor=996041337&empresa=29409713
});
