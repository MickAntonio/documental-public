<?php

namespace App\Http\Controllers\Encaminhamento;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Encaminhamento;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\EncaminhamentoPendencia;
use Illuminate\Support\Facades\Validator;
use App\Models\EncaminhamentoDestinatario;

class EncaminhamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $encaminhamentos = Encaminhamento::with([
                'utilizador',
                'destinatarios',
                'destinatarios.utilizador',
                'destinatarios.entidade'
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
                'accao'             => 'required',
                'accaoObrigatoriaTodos'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = new Encaminhamento();
            $encaminhamento->assunto            = $request->assunto;
            $encaminhamento->registoId          = $request->registoId;
            $encaminhamento->utilizadorId       = $request->utilizadorId;
            $encaminhamento->mensagem           = $request->mensagem;
            $encaminhamento->dataLimite         = date('Y-m-d', strtotime($request->dataLimite));
            $encaminhamento->enviarEmail        = $request->enviarEmail;
            $encaminhamento->receberNotificacao = $request->receberNotificacao;
            $encaminhamento->accaoSolicitada    = $request->accao;
            $encaminhamento->obrigatorioTodos   = $request->accaoObrigatoriaTodos;
            $encaminhamento->save();

            foreach ($request->encaminharDestinatario as $destinatario) {

                $encaminhamentoDestinatario = new EncaminhamentoDestinatario();
                $encaminhamentoDestinatario->encaminhamentoId   = $encaminhamento->id;
                $encaminhamentoDestinatario->visualizado        = false;
                $encaminhamentoDestinatario->tipo               = $destinatario['tipo'];
                if($destinatario['tipo']=='UTILIZADOR'){
                    $encaminhamentoDestinatario->utilizadorId   = $destinatario['utilizadorId'];
                }else if($destinatario['tipo']=='GRUPO'){
                    $encaminhamentoDestinatario->grupoId        = $destinatario['grupoId'];
                }else if($destinatario['tipo']=='ENTIDADE'){
                    $encaminhamentoDestinatario->entidadeId        = $destinatario['entidadeId'];
                }
                $encaminhamentoDestinatario->save();
            }

            if($request->enviarEmail){
                Mail::send(new EncaminhamentoPendencia($encaminhamento));
            }

            return response()->json($encaminhamento)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'           => 'required',
                'assunto'           => 'required',
                'registoId'         => 'required',
                'utilizadorId'      => 'required',
                'accaoSolicitada'   => 'required',
                'obrigatorioTodos'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = Encaminhamento::find($request->id);
            if($encaminhamento == null) {
                return response()->json(['message' => 'Encaminhamento não encontrada'], Response::HTTP_NOT_FOUND);
            }
            $encaminhamento->assunto            = $request->assunto;
            $encaminhamento->registoId          = $request->registoId;
            $encaminhamento->utilizadorId       = $request->utilizadorId;
            $encaminhamento->mensagem           = $request->mensagem;
            $encaminhamento->dataLimite         = $request->dataLimite;
            $encaminhamento->enviarEmail        = $request->enviarEmail;
            $encaminhamento->receberNotificacao = $request->receberNotificacao;
            $encaminhamento->accaoSolicitada    = $request->accaoSolicitada;
            $encaminhamento->accaoExecutada     = $request->accaoExecutada;
            $encaminhamento->obrigatorioTodos   = $request->obrigatorioTodos;
            $encaminhamento->save();

            return response()->json($encaminhamento)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAccao(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'             => 'required',
                'accaoExecutada' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = Encaminhamento::find($request->id);
            if($encaminhamento == null) {
                return response()->json(['message' => 'Encaminhamento não encontrada'], Response::HTTP_NOT_FOUND);
            }
            $encaminhamento->accaoExecutada     = $request->accaoExecutada;
            $encaminhamento->save();

            return response()->json($encaminhamento)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
