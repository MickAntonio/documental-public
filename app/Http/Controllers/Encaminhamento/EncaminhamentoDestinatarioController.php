<?php

namespace App\Http\Controllers\Encaminhamento;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $encaminhamentos = EncaminhamentoDestinatario::where('id', '>', 0);

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $encaminhamentos->orderBy(request('sort'), request('order'));
                }
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $encaminhamentos = $encaminhamentos->paginate($encaminhamentos->count(), ['*'], 'page', 1);

                return response()->json(repage($encaminhamentos))->setStatusCode(Response::HTTP_OK);
            }

            $encaminhamentos = $encaminhamentos->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($encaminhamentos))->setStatusCode(Response::HTTP_OK);
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
                'encaminhamentoId' => 'required',
                'accao'            => 'required',
                'tipo'             => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $destinatario = new EncaminhamentoDestinatario();
            $destinatario->encaminhamentoId   = $request->encaminhamentoId;
            $destinatario->visualizado        = $request->visualizado;
            $destinatario->mensagem           = $request->mensagem;
            $destinatario->accao              = $request->accao;
            $destinatario->tipo               = $request->tipo;
            $destinatario->utilizadorId       = Auth::user()->id;
            $destinatario->grupoId            = $request->grupoId;
            $destinatario->entidadeId         = $request->entidadeId;
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
    public function update(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'encaminhamentoId' => 'required',
                'accao'            => 'required',
                'tipo'             => 'required'
            ]);


            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $destinatario = EncaminhamentoDestinatario::find($request->id);
            if($destinatario == null) {
                return response()->json(['message' => 'Encaminhamento Destinatario não encontrada'], Response::HTTP_NOT_FOUND);
            }
            $destinatario->encaminhamentoId   = $request->encaminhamentoId;
            $destinatario->visualizado        = $request->visualizado;
            $destinatario->mensagem           = $request->mensagem;
            $destinatario->accao              = $request->accao;
            $destinatario->tipo               = $request->tipo;
            $destinatario->utilizadorId       = Auth::user()->id;
            $destinatario->grupoId            = $request->grupoId;
            $destinatario->entidadeId         = $request->entidadeId;
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
                'mensagem'     => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $encaminhamento = Encaminhamento::find($request->id);
            if($encaminhamento == null) {
                return response()->json(['message' => 'Encaminhamento não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $userGroups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');

            $destinatarios = EncaminhamentoDestinatario::with(['utilizador.utilizadoresInterinos'])->where('encaminhamentoId', $request->id)
            ->where(function (Builder $query) use ($userGroups) {
                $query->where('utilizadorId',  Auth::user()->id)
                    ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                        $query->where('utilizadorInterinoId', Auth::user()->id);
                    })
                    ->orWhereIn('grupoId', $userGroups);
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
                    $destinatario->utilizadorId  = Auth::user()->id;
                    $destinatario->save();
                }else{
                    $destinatario = new EncaminhamentoDestinatario();
                    $destinatario->accao            = $request->accao;
                    $destinatario->mensagem         = $request->mensagem;
                    $destinatario->pendente         = false;
                    $destinatario->utilizadorId     = Auth::user()->id;
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
