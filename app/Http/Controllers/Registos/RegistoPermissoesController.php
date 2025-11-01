<?php

namespace App\Http\Controllers\Registos;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\RegistoPermissoes;
use App\Http\Controllers\Controller;
use OwenIt\Auditing\Events\AuditCustom;
use Illuminate\Support\Facades\Validator;

class RegistoPermissoesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $estados = RegistoPermissoes::with(['entidade', 'utilizador'])->where('id', '>', 0);

            if(request('registoId') && !isNullOrEmpty(request('registoId'))){
                $estados->where('registoId', request('registoId'));
            }
           
            if(request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))){
                $estados->where(request('column'), 'like', '%'.request('value').'%');
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $estados->orderBy(request('sort'), request('order'));
                }
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $estados = $estados->paginate($estados->count(), ['*'], 'page', 1);

                return response()->json(repage($estados))->setStatusCode(Response::HTTP_OK);
            }

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
                'registoUtilizadores'  => 'required',
                'permissoes'  => 'required',
                'registoId'  => 'required'
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $registoPermissao = [];

            foreach ($request->registoUtilizadores as $utilizador) {
                $registoPermissao = new RegistoPermissoes();
                $tipoUtilizador = $utilizador["tipo"];
                if ($tipoUtilizador == "UTILIZADOR") {
                    $registoPermissao->utilizadorId  = $utilizador["utilizadorId"];
                } else if ($tipoUtilizador == "ENTIDADE") {
                    $registoPermissao->entidadeId  = $utilizador["entidadeId"];
                } else if ($tipoUtilizador == "GRUPO") {
                    $registoPermissao->grupoId = $utilizador["grupoId"];
                }

                $registoPermissao->tipo       = $tipoUtilizador;
                $registoPermissao->registoId  = $request->registoId;
                $registoPermissao->permissoes = implode(',', $request->permissoes);
                $registoPermissao->save();

                $registoPermissoes[] = $registoPermissao;
            }

            return response()->json($registoPermissoes)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   

    /**
     * Remove the specified resource from storage.
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
            $registoPermissao = RegistoPermissoes::findOrFail($id);

            if ($registoPermissao == null) {
                return response()->json(['message' => 'Estado não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $registoPermissao->delete();
            return response()->json(['message' => 'Estado excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
