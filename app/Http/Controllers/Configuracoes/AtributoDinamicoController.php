<?php

namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\AtributoDinamico;

class AtributoDinamicoController extends Controller
{
    public function index()
    {
        try {

            $atributoDinamico = AtributoDinamico::where('id', '>', 0);

            if(request('key') && !isNullOrEmpty(request('key'))){
                $atributoDinamico->where('key', 'like', '%'.request('nome').'%');
            }

            if(request('value') && !isNullOrEmpty(request('value'))){
                $atributoDinamico->where('value', 'like', '%'.request('value').'%');
            }

            if(request('label') && !isNullOrEmpty(request('label'))){
                $atributoDinamico->where('label', 'like', '%'.request('label').'%');
            }

            if(request('type') && !isNullOrEmpty(request('type'))){
                $atributoDinamico->where('type', 'like', '%'.request('type').'%');
            }

            if(request('required') && !isNullOrEmpty(request('required'))){
                $atributoDinamico->where('required', 'like', '%'.request('required').'%');
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                $atributoDinamico->where('order', 'like', '%'.request('order').'%');
            }

            if(request('controlType') && !isNullOrEmpty(request('controlType'))){
                $atributoDinamico->where('controlType', 'like', '%'.request('controlType').'%');
            }

            if(request('options') && !isNullOrEmpty(request('options'))){
                $atributoDinamico->where('options', 'like', '%'.request('options').'%');
            }

            if(request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd')) ){
                $from = date_format(date_create(request('dtInitial').'00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd').'23:59:59'), 'Y-m-d H:i:s');
                $atributoDinamico->whereBetween('created_at', [$from, $to]);
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $atributoDinamico->orderBy(request('sort'), request('order'));
                }
            }
            
            if(request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))){
                $atributoDinamico->where(request('column'), 'like', '%'.request('value').'%');
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $atributoDinamico = $atributoDinamico->paginate($atributoDinamico->count(), ['*'], 'page', 1);
    
                return response()->json(repage($atributoDinamico))->setStatusCode(Response::HTTP_OK);
            }

            $atributoDinamico = $atributoDinamico->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($atributoDinamico))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'key'  => 'required|string|max:100',
                'value'  => 'required|string|max:100',
                'label'  => 'required|string|max:100',
                'required'  => 'required|string|max:100',
                'type'  => 'required|string|max:100',
                'order'  => 'required|string|max:100',
                'controlType'  => 'required|string',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

                $atributoDinamico = new AtributoDinamico();

                $atributoDinamico->key          = $request->key;
                $atributoDinamico->value        = $request->value;
                $atributoDinamico->label        = $request->label;
                $atributoDinamico->required     = $request->required;            
                $atributoDinamico->type         = $request->type; 
                $atributoDinamico->order        = $request->order;
                $atributoDinamico->controlType  = $request->controlType;
                $atributoDinamico->options      = $request->options;
                $atributoDinamico->save();

                return response()->json($atributoDinamico)->setStatusCode(Response::HTTP_CREATED);

            return response()->json($atributoDinamico)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            if ($id == null || $id <= 0) {
               return response()->json(['message' => 'id não valido'], Response::HTTP_BAD_REQUEST);
            }
            $atributoDinamico = AtributoDinamico::find($id);

            if ($atributoDinamico == null) {
                return response()->json(['message' => 'Atributo Dinamico não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $atributoDinamico], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'id'  => 'required|integer',
                'key'  => 'required|string|max:100',
                'value'  => 'required|string|max:100',
                'label'  => 'required|string|max:100',
                'required'  => 'required|string|max:100',
                'type'  => 'required|string|max:100',
                'order'  => 'required|string|max:100',
                'controlType'  => 'required|string',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $atributoDinamico = AtributoDinamico::find($request->id);
            if($atributoDinamico == null) {
                return response()->json(['message' => 'Atributo dinamico não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $atributoDinamico->key          = $request->key;
            $atributoDinamico->value        = $request->value;
            $atributoDinamico->label        = $request->label;
            $atributoDinamico->required     = $request->required;            
            $atributoDinamico->type         = $request->type; 
            $atributoDinamico->order        = $request->order;
            $atributoDinamico->controlType  = $request->controlType;
            $atributoDinamico->options      = $request->options;
            $atributoDinamico->save();

            return response()->json($atributoDinamico)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            if ($id == null || $id <= 0) {
                return response()->json(['message' => 'id não valido'], Response::HTTP_BAD_REQUEST);
             }

            $atributoDinamico = AtributoDinamico::findOrFail($id);

            if ($atributoDinamico == null) {    
                return response()->json(['message' => 'Atributo dinamico não encontrada'], Response::HTTP_NOT_FOUND);
            }
            
            $atributoDinamico->delete();
            return response()->json(['message' => 'Atributo dinamico excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
