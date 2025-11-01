<?php

namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\EntidadeTipo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class EntidadeTipoController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $entidadeTipos = EntidadeTipo::where('id', '>', 0);

            if(request('nome') && !isNullOrEmpty(request('nome'))){
                $entidadeTipos->where('nome', 'like', '%'.request('nome').'%');
            }

            if(request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd')) ){
                $from = date_format(date_create(request('dtInitial').'00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd').'23:59:59'), 'Y-m-d H:i:s');
                $entidadeTipos->whereBetween('created_at', [$from, $to]);
            }

            if(request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))){
                $entidadeTipos->where(request('column'), 'like', '%'.request('value').'%');
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $entidadeTipos->orderBy(request('sort'), request('order'));
                }
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $entidadeTipos = $entidadeTipos->paginate($entidadeTipos->count(), ['*'], 'page', 1);
    
                return response()->json(repage($entidadeTipos))->setStatusCode(Response::HTTP_OK);
            }

            $entidadeTipos = $entidadeTipos->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($entidadeTipos))->setStatusCode(Response::HTTP_OK);
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
                'nome'  => 'required|string|max:45|unique:entidade_tipos,nome',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $entidadeTipo = new EntidadeTipo();
            $entidadeTipo->nome = $request->nome;
            $entidadeTipo->save();

            return response()->json($entidadeTipo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            // $entidadeTipo = EntidadeTipo::with('entidadeTipoId')->find($id);
            $entidadeTipo = EntidadeTipo::find($id);

            if ($entidadeTipo == null) {
                return response()->json(['message' => 'Tipo de Entidade não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $entidadeTipo], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'nome'  => 'required|string|max:45|unique:entidade_tipos,nome,'. $request->id .'null',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $entidadeTipo = EntidadeTipo::find($request->id);
            if($entidadeTipo == null) {
                return response()->json(['message' => 'EntidadeTipo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $entidadeTipo->nome = $request->nome;
            $entidadeTipo->save();

            return response()->json($entidadeTipo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $entidadeTipo = EntidadeTipo::findOrFail($id);

            if ($entidadeTipo == null) {    
                return response()->json(['message' => 'Tipo de Entidade não encontrada'], Response::HTTP_NOT_FOUND);
            }
            
            $entidadeTipo->delete();
            return response()->json(['message' => 'Tipo de Entidade excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
