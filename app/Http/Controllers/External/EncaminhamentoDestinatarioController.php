<?php

namespace App\Http\Controllers\External;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Encaminhamento;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\EncaminhamentoDestinatario;

class EncaminhamentoDestinatarioController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateVisualizado(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'             => 'required',
                'visualizado' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $destinatario = EncaminhamentoDestinatario::find($request->id);
            if($destinatario == null) {
                return response()->json(['message' => 'Encaminhamento Destinatario não encontrada'], Response::HTTP_NOT_FOUND);
            }
            $destinatario->visualizado = $request->visualizado;
            $destinatario->save();

            return response()->json($destinatario)->setStatusCode(Response::HTTP_CREATED);
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
    public function executarAccao(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'           => 'required',
                'accao'        => 'required',
                'mensagem'     => 'required',
                'entidadeId'   => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = Encaminhamento::find($request->id);
            if($encaminhamento == null) {
                return response()->json(['message' => 'Encaminhamento não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $destinatarios = EncaminhamentoDestinatario::where('encaminhamentoId', $request->id)
            ->where(function (Builder $query) use ($request) {
                $query->where('entidadeId',  $request->entidadeId);
            })->get();
            
            
            if($destinatarios == null) {
                return response()->json(['message' => 'Encaminhamento Destinatario não encontrada'], Response::HTTP_NOT_FOUND);
            }

            foreach ($destinatarios as $value) {

                $destinatario = EncaminhamentoDestinatario::find($value->id);
                if($value->pendente){
                    $destinatario->accao         = $request->accao;
                    $destinatario->mensagem      = $request->mensagem;
                    $destinatario->pendente      = false;
                    $destinatario->entidadeId    = $request->entidadeId;
                    $destinatario->save();
                }else{
                    $destinatario = new EncaminhamentoDestinatario();
                    $destinatario->accao            = $request->accao;
                    $destinatario->mensagem         = $request->mensagem;
                    $destinatario->pendente         = false;
                    $destinatario->entidadeId       = $request->entidadeId;
                    $destinatario->encaminhamentoId = $value->encaminhamentoId;
                    $destinatario->grupoId          = $value->grupoId;
                    $destinatario->save();
                }
              
            }

            if($encaminhamento->obrigatorioTodos){
                
                $destinatariosPendentes = EncaminhamentoDestinatario::where('encaminhamentoId', $request->id)->where('pendente', true)->count();
            
                if($destinatariosPendentes==0){
                    $encaminhamento->pendente = false;
                    $encaminhamento->accaoExecutada = $request->accao;
                    $encaminhamento->save();
                }
            }else{
                $encaminhamento->pendente = false;
                $encaminhamento->accaoExecutada  = $request->accao;
                $encaminhamento->save();
            }

            return response()->json($destinatario)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
