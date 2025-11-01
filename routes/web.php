<?php

use Illuminate\Http\Response;
use Keycloak\Admin\KeycloakClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Configuracoes\AtributoDinamicoController;
use App\Http\Controllers\Registos\RegistoController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::view('/documentation', 'swagger-ui/index');
Route::view('/documentation/redirect', 'swagger-ui/redirect');
Route::apiResource('/entradas',RegistoController::class);
