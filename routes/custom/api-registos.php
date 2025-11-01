<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Registos\EstadoController;
use App\Http\Controllers\Registos\RegistoController;
use App\Http\Controllers\Registos\RegistoPermissoesController;
use App\Http\Controllers\Encaminhamento\EncaminhamentoController;
use App\Http\Controllers\Encaminhamento\EncaminhamentoExternoController;
use App\Http\Controllers\Encaminhamento\EncaminhamentoDestinatarioController;

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

//Route::group(['middleware' => 'auth'], function() {
    Route::apiResource('/entradas', RegistoController::class);
    Route::apiResource('/estados', EstadoController::class);
    Route::get('/entradas-detail/{id}', [RegistoController::class, 'showDetailDocumentById']);
    Route::get('/entradas-detail-refresh/{id}', [RegistoController::class, 'showDetailDocumentByIdRefresh']);
    Route::get('/pesquisa-documento', [RegistoController::class, 'seachAdvancedDocument']);

    Route::get('/encaminhamentos', [EncaminhamentoController::class, 'index']);
    Route::post('/encaminhamentos', [EncaminhamentoController::class, 'store']);
    Route::put('/encaminhamentos-accao', [EncaminhamentoController::class, 'updateAccao']);

    Route::get('/encaminhamento-destinatarios', [EncaminhamentoDestinatarioController::class, 'store']);
    Route::post('/encaminhamento-destinatarios', [EncaminhamentoDestinatarioController::class, 'index']);
    Route::put('/encaminhamento-destinatarios-accao', [EncaminhamentoDestinatarioController::class, 'executarAccao']);

    Route::get('/encaminhamentos-total-pendentes', [RegistoController::class, 'totalPendentes']);
    Route::get('/registo/participantes', [RegistoController::class, 'indexParticipantes']);
    Route::put('/registo/update-estado', [RegistoController::class, 'updateEstado']);

    Route::post('/encaminhamentos-externo', [EncaminhamentoExternoController::class, 'store']);

    Route::get('/registo-permissoes', [RegistoPermissoesController::class, 'index']);
    Route::post('/registo-permissoes', [RegistoPermissoesController::class, 'store']);
    Route::delete('/registo-permissoes/{id}', [RegistoPermissoesController::class, 'destroy']);


//});

Route::post('entradas/audit', [RegistoController::class, 'auditSawEvent']);


