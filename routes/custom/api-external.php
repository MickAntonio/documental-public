<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Anexos\AnexosController;
use App\Http\Controllers\External\RegistoController;
use App\Http\Middleware\AuthenticatePortalEntidades;
use App\Http\Controllers\Configuracoes\TipoController;
use App\Http\Controllers\Encaminhamento\EncaminhamentoController;
use App\Http\Controllers\External\EncaminhamentoDestinatarioController;

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


Route::prefix('external')->middleware([AuthenticatePortalEntidades::class])->group(function () {

    Route::get('documentos/pendentes', [RegistoController::class, 'index']);
    Route::get('/encaminhamentos', [EncaminhamentoController::class, 'index']);
    Route::put('/encaminhamentos-accao', [EncaminhamentoController::class, 'updateAccao']);
    Route::put('/encaminhamento-destinatarios-accao', [EncaminhamentoDestinatarioController::class, 'executarAccao']);
    Route::get('/entradas-detail/{id}', [RegistoController::class, 'showDetailDocumentById']);
    Route::get('/anexos/view/{file}', [AnexosController::class, 'view']);
    Route::get('/encaminhamentos-total-pendentes', [RegistoController::class, 'totalPendentes']);
    Route::get('/tipos', [TipoController::class, 'index']);

});