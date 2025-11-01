<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\LocalizacaoScanner;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Email\EmailController;
use App\Http\Controllers\Anexos\AnexosController;
use App\Http\Controllers\Api\Auth\EmpresaController;
use App\Http\Controllers\Anexos\AssinaturaController;
use App\Http\Controllers\EtiquetaProtocoloController;
use App\Http\Controllers\Anexos\AnexoPendenteController;
use App\Http\Controllers\Anexos\HistoricoAnexoController;
use App\Http\Controllers\Configuracoes\TemplateController;
use App\Http\Controllers\Anexos\LocalizacaoScannerController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/anexos/download/{file}/{utilizadorId}', [AnexosController::class, 'download']);
Route::post('/anexos/download/multiples', [AnexosController::class, 'downloadMultiples']);
Route::get('/anexos/view/{file}', [AnexosController::class, 'view']);
Route::get('/anexos/historico/view/{file}', [HistoricoAnexoController::class, 'view']);
Route::get('/anexos/checkout/{file}', [AnexosController::class, 'checkOut']);
Route::get('/anexos/checkin/{file}', [AnexosController::class, 'checkIn']);
Route::get('/anexos/url/{file}', [AnexosController::class, 'url']);
Route::post('/anexos/upload/draganddrop', [AnexosController::class, 'storeDragDrop']);
Route::put('/anexos/update/metadados', [AnexosController::class, 'updateMetedados']);
Route::delete('/anexos/{id}', [AnexosController::class, 'destroy']);
Route::post('/anexos/multiples', [AnexosController::class, 'destroyMultiples']);
Route::post('/anexos/template/generate', [AnexosController::class, 'templateFile']);
Route::post('/anexos/update/office', [AnexosController::class, 'addInDocumentChange']);
Route::post('/anexos/add/template', [AnexosController::class, 'storeAnexoTemplate']);


Route::post('/assinatura', [AssinaturaController::class, 'assinar']);

Route::post('/anexos/convert', [HistoricoAnexoController::class, 'convertToPdf']);
Route::get('/folders', [HistoricoAnexoController::class, 'folders']);
Route::get('/anexos/path-name', [HistoricoAnexoController::class, 'getAnexo']);
Route::get('/anexos/path-name/download', [HistoricoAnexoController::class, 'getAnexoDownload']);

Route::get('/send-email', [EmailController::class, 'senMail']);

Route::get('/anexos/pendentes', [AnexoPendenteController::class, 'index']);
Route::post('/anexos/pendentes/add', [AnexoPendenteController::class, 'store']);
Route::get('/anexos/pendentes/view/{file}', [AnexoPendenteController::class, 'view']);

Route::get('/scanners', [LocalizacaoScannerController::class, 'index']);

Route::get('/etiquetas/protocolos', [EtiquetaProtocoloController::class, 'index']);
Route::post('/etiquetas/protocolos', [EtiquetaProtocoloController::class, 'store']);
Route::get('/etiquetas/protocolos/pdf', [EtiquetaProtocoloController::class, 'pdf']);
Route::get('/etiquetas/protocolos/download/{id}', [EtiquetaProtocoloController::class, 'download']);
Route::get('/etiquetas/protocolos/validar-protocolo/{id}', [EtiquetaProtocoloController::class, 'validarProtocolo']);

Route::post('/update-image-profile', [UserController::class, 'updateImageProfile']);







