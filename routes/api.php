<?php

use App\Http\Controllers\ContrladoresDocentes\ControladorJefatura;
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


Route::group(['prefix' => 'jefatura'], function () {
    //Por si se me olvida a posteriorri: está puesto como un get para pruebas,
    //por favor, cambiar a post
    Route::get('recibirCSV', [ControladorJefatura::class, 'recibirCSV']);
});

