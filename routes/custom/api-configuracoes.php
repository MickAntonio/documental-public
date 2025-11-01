<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Configuracoes\EmpresaController;
use App\Http\Controllers\Configuracoes\EntidadeController;
use App\Http\Controllers\Configuracoes\TemplateController;
use App\Http\Controllers\Configuracoes\EntidadeTipoController;
use App\Http\Controllers\Configuracoes\TipoController;
use App\Http\Controllers\Configuracoes\AtributoDinamicoController;

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

// TEMPLATE
Route::group(['prefix' => 'templates'], function() {
    Route::middleware(['auth'])->get('/', [TemplateController::class, 'index']);
    Route::middleware(['auth'])->get('/{id}', [TemplateController::class, 'show']);
    Route::middleware(['auth'])->post('/', [TemplateController::class, 'store']);
    Route::middleware(['auth'])->put('/{id}', [TemplateController::class, 'update']);
    Route::middleware(['auth'])->delete('/{id}', [TemplateController::class, 'destroy']);
});

Route::group(['middleware' => 'auth'], function() {
    Route::apiResource('/entidades', EntidadeController::class);
    Route::apiResource('/tipo-entidades', EntidadeTipoController::class);
    Route::apiResource('/empresas', EmpresaController::class);
    Route::apiResource('/tipos', TipoController::class);
    Route::apiResource('/atributo-dinamicos', AtributoDinamicoController::class);

});

