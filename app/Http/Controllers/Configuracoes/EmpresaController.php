<?php

namespace App\Http\Controllers\Configuracoes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Empresa;

class EmpresaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        try {
            $empresas = Empresa::where('id', '>', 0);

            if (request('nome') &&  !isNullOrEmpty(request('nome'))) {
                $empresas->where('nome', 'like', '%' . request('nome') . '%');
            }

            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {
                $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                $empresas->whereBetween('created_at', [$from, $to]);
            }

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $empresas->where(request('column'), 'like', '%' . request('value') . '%');
            }
            $empresas = $empresas->paginate(request('size'), ['*'], 'page', request('page') + 1);


            return response()->json(repage($empresas))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $empresa = Empresa::where('id', '>', 0)->first();

            if ($empresa != null) {
                return response()->json(['data' => $empresa], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Empresa não encotrada'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showOne()
    {
        try {

            $empresa = Empresa::where('id', '>', 0)->first();

            if ($empresa != null) {
                return response()->json(['data' => $empresa], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Empresa não encotrada'], Response::HTTP_NOT_FOUND);
            }
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
            $validator      = Validator::make(
                $request->all(),
                [
                    'nome' => 'required|string|max:100',
                    'nif' => 'sometimes|string|max:100',
                    'email' => 'required|email',
                    'telefone' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            $produto =  Empresa::where('nome', $request->nome)->get()->first();
            if (!isset($produto->nome)) {
                $empresa =   new Empresa();

                $empresa->nome                  =   $request->nome;
                $empresa->nif                   =   $request->nif;
                $empresa->email                 =   $request->email;
                $empresa->telefone              =   $request->telefone;
                $empresa->actividadesEconomica  =   $request->actividadesEconomica;
                $empresa->naturezaJuridica      =   $request->naturezaJuridica;
                $empresa->endereco              =   $request->endereco;
                $empresa->dataAbertura          =   $request->dataAbertura;
                $empresa->save();
            } else {
                $empresa = $produto;
            }
            return response()->json(['data' => $empresa])->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $validator      = Validator::make(
                $request->all(),
                [
                    'nome' => 'required|string|max:100',
                    'nif' => 'sometimes|string|max:100',
                    'email' => 'required|email',
                    'telefone' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $empresa = Empresa::find($request->id);

            if ($empresa != null) {
                $empresa->nome                  =   $request->nome;
                $empresa->nif                   =   $request->nif;
                $empresa->email                 =   $request->email;
                $empresa->telefone              =   $request->telefone;
                $empresa->actividadesEconomica  =   $request->actividadesEconomica;
                $empresa->naturezaJuridica      =   $request->naturezaJuridica;
                $empresa->endereco              =   $request->endereco;
                $empresa->dataAbertura          =   $request->dataAbertura;
                $empresa->save();

                return response()->json(['data' => $empresa])->setStatusCode(Response::HTTP_CREATED);
            } else {
                return response()->json(['message' => 'Empresa não encotrada'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $empresa = Empresa::find($id);

            if ($empresa != null) {
                $empresa->delete();
                return response()->json(['data' => $empresa])->setStatusCode(Response::HTTP_CREATED);
            } else {
                return response()->json(['message' => 'Empresa não encotrada'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
