<?php

namespace App\Http\Controllers\Encaminhamento;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\EncaminhamentoExterno;
use Illuminate\Support\Facades\Validator;

class EncaminhamentoExternoController extends Controller
{
 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $encaminhamentos = EncaminhamentoExterno::with([
                'utilizador',
                'registo'
            ]);

            if (request('registoId') && !isNullOrEmpty(request('registoId'))) {
                $encaminhamentos->where('registoId', request('registoId'));
            }
            
            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $encaminhamentos->orderBy(request('sort'), request('order'));
                }
            }

            return response()->json($encaminhamentos->get())->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'assunto'           => 'required',
                'registoId'         => 'required',
                'utilizadorId'      => 'required',
                'mensagem'          => 'required',
                'to'                => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = new EncaminhamentoExterno();
            $encaminhamento->assunto      = $request->assunto;
            $encaminhamento->registoId    = $request->registoId;
            $encaminhamento->utilizadorId = $request->utilizadorId;
            $encaminhamento->mensagem     = $request->mensagem;
            $encaminhamento->to           = $request->to;
            $encaminhamento->cc           = $request->cc;
            $encaminhamento->bcc          = $request->bcc;
            $encaminhamento->save();

            Mail::send(new \App\Mail\EncaminhamentoExterno($encaminhamento));

            return response()->json($encaminhamento)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
