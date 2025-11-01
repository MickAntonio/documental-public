<?php

namespace App\Http\Controllers\Admin;

use App\Models\UserInterino;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Registo;
use Illuminate\Support\Facades\Auth;
use App\Models\EncaminhamentoDestinatario;
use Illuminate\Database\Eloquent\Builder;

class UserInterinoController extends Controller
{

    public function teste()
    {
        try {

            $userGroups =  null;// collect(KeycloakClient()->getUserGroups(['id' => '9f373fca-ace6-4309-8999-ad4f7548c96d']))->pluck('id');


            $destinatarios = EncaminhamentoDestinatario::with(['utilizador.utilizadoresInterinos'])->where('encaminhamentoId', 13)
            ->where(function (Builder $query) use ($userGroups) {
                $query->where('utilizadorId',  '9f373fca-ace6-4309-8999-ad4f7548c96d')
                    ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                        $query->where('utilizadorInterinoId', '9f373fca-ace6-4309-8999-ad4f7548c96d');
                    });
            })->get();


            /*
            $registos = Registo::with([
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.utilizador',
                'encaminhamentos.destinatarios',
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.utilizador.utilizadoresInterinos'
            ])
            ->where('id', '>', 0);

            $registos = $registos->whereHas('encaminhamentos', function ($encaminhamento) {
                $encaminhamento->when('obrigatorioTodos' == true, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {

                        $destinatario->where('pendente', true)->where('utilizadorId', 'ad3e99fc-e0ab-4441-9163-ae408551633ad')
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', 'ad3e99fc-e0ab-4441-9163-ae408551633a');
                        })
                        ->where('pendente', true);
                    })->where('pendente', true);
                }, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {
                        $destinatario->where('utilizadorId', 'e9e91e6d-1bfe-4fc4-8da1-3eb6d162799b')
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', 'ad3e99fc-e0ab-4441-9163-ae408551633a');
                        });
                    })->where('pendente', true);
                });
            });
            
            $registos->orderBy('id', 'desc');

            $registos = $registos->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($registos))->setStatusCode(Response::HTTP_OK);*/
            return response()->json($destinatarios)->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index()
    {
        try {

            $estados = UserInterino::with(['utilizador', 'utilizadorInterino'])->where('id', '>', 0);

            if(request('utilizadorId') && !isNullOrEmpty(request('utilizadorId'))){
                $estados->where('utilizadorId', request('utilizadorId'));
            }

            if(request('utilizadorInterinoId') && !isNullOrEmpty(request('utilizadorInterinoId'))){
                $estados->where('utilizadorInterinoId', request('utilizadorInterinoId'));
            }
            
            $estados->orderBy('id', 'desc');

            $estados = $estados->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($estados))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'utilizadorId'         => 'required',
                'utilizadorInterinoId' => 'required',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $userInterino = new UserInterino();
            $userInterino->utilizadorId         = $request->utilizadorId;
            $userInterino->utilizadorInterinoId = $request->utilizadorInterinoId;
            $userInterino->periodoInicio = date('Y-m-d', strtotime($request->periodoInicio));
            $userInterino->periodoFim = date('Y-m-d', strtotime($request->periodoFim));
            $userInterino->save();

            return response()->json($userInterino)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $validator = Validator::make(['id'=>$id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            $estado = UserInterino::findOrFail($id);

            if ($estado == null) {
                return response()->json(['message' => 'Estado não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $estado->delete();
            return response()->json(['message' => 'Estado excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}