<?php

namespace App\Http\Controllers\Registos;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class EstadoController extends Controller
{

    public function index()
    {
        try {

            $estados = Estado::where('id', '>', 0);

            if(request('nome') && !isNullOrEmpty(request('nome'))){
                $estados->where('nome', 'like', '%'.request('nome').'%');
            }
             if(request('type') && !isNullOrEmpty(request('type'))){
                $estados->where('type', 'like', '%'.request('type').'%');
            }

            if(request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd')) ){
                $from = date_format(date_create(request('dtInitial').'00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd').'23:59:59'), 'Y-m-d H:i:s');
                $estados->whereBetween('created_at', [$from, $to]);
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
                'nome'  => 'required|string|max:100',
                'type'  => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $estado = new Estado();
            $estado->nome = $request->nome;
            $estado->type = $request->type;
            $estado->save();

            return response()->json($estado)->setStatusCode(Response::HTTP_CREATED);
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
            $validator = Validator::make(['id'=>$id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $estado = Estado::find($id);

            if ($estado == null) {
                return response()->json(['message' => 'Estado não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $estado], Response::HTTP_OK);
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
                'id'=>'required|integer',
                'nome'  => 'required|string|max:100',
                'type'  => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $estado = Estado::find($request->id);
            if($estado == null) {
                return response()->json(['message' => 'esta$estado não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $estado->nome = $request->nome;
            $estado->type = $request->type;
            $estado->save();

            return response()->json($estado)->setStatusCode(Response::HTTP_CREATED);
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
            $estado = Estado::findOrFail($id);

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
