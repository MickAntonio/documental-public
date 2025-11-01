<?php

namespace App\Http\Controllers\Anexos;

use App\Models\LocalizacaoScanner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class LocalizacaoScannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $anexosPendentes = LocalizacaoScanner::get();

            return response()->json($anexosPendentes)->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   
}
