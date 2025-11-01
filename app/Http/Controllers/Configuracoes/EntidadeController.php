<?php

namespace App\Http\Controllers\Configuracoes;

use App\Models\User;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EntidadeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            $entidades = Entidade::where('id', '>', 0)->with('entidadeTipo');

            if(request('nome') && !isNullOrEmpty(request('nome'))){
                $entidades->where('nome', 'like', '%'.request('nome').'%');
            }

            if(request('codigo') && !isNullOrEmpty(request('codigo'))){
                $entidades->where('codigo', 'like', '%'.request('codigo').'%');
            }

            if(request('nif') && !isNullOrEmpty(request('nif'))){
                $entidades->where('nif', 'like', '%'.request('nif').'%');
            }

            if(request('email') && !isNullOrEmpty(request('email'))){
                $entidades->where('email', 'like', '%'.request('email').'%');
            }

            if(request('telefone') && !isNullOrEmpty(request('telefone'))){
                $entidades->where('telefone', 'like', '%'.request('telefone').'%');
            }

            if(request('endereco') && !isNullOrEmpty(request('endereco'))){
                $entidades->where('endereco', 'like', '%'.request('endereco').'%');
            }


            if(request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd')) ){
                $from = date_format(date_create(request('dtInitial').'00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd').'23:59:59'), 'Y-m-d H:i:s');
                $entidades->whereBetween('created_at', [$from, $to]);
            }

            if(request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))){
                $entidades->where(request('column'), 'like', '%'.request('value').'%');
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $entidades->orderBy(request('sort'), request('order'));
                }
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $entidades = $entidades->paginate($entidades->count(), ['*'], 'page', 1);
    
                return response()->json(repage($entidades))->setStatusCode(Response::HTTP_OK);
            }

            $entidades = $entidades->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($entidades))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'nome'  => 'required|string|max:45',
                'codigo'  => 'required|string|max:45',
                'nif'  => 'required|string|max:45|unique:entidades,nif',
                'activo'  => 'required|integer',
                'endereco'  => 'required|string|max:45',
                'email'  => 'required|email|max:45|unique:entidades,email',
                'telefone'  => 'required|string|max:45|unique:entidades,telefone',
                'entidadeTipoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $entidade = new Entidade();
            $entidade->nome = $request->nome;
            $entidade->codigo = $request->codigo;
            $entidade->nif = $request->nif;
            $entidade->activo = $request->activo;
            $entidade->endereco = $request->endereco;
            $entidade->email = $request->email;
            $entidade->telefone = $request->telefone;
            $entidade->entidadeTipoId = $request->entidadeTipoId;
            $entidade->save();

            if($request->accesso){
                $utilizador = $this->storeUser($request, $entidade);

                if($utilizador!=null){
                    $entidade->utilizadorId = $utilizador->id;
                    $entidade->save();
                }
            }

            return response()->json($entidade)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function storeUser(Request $request, $entidade){

        $username = env('PORTAL_ENTIDADE_USERNAME');
        $password = env('PORTAL_ENTIDADE_PASSWORD');

        $credentials = $username . ':' . $password;
        $encodedCredentials = base64_encode($credentials);

        $body = [
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'nome' => $entidade->nome,
            'entidade' => $entidade->id,
            'activo' => $entidade->activo
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedCredentials,
        ])->post('http://localhost:8001/admin/users', $body);
    
        return $response->object();
    
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

            $entidade = Entidade::with('entidadeTipo')->find($id);

            if ($entidade == null) {
                return response()->json(['message' => 'Entidade não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $entidade], Response::HTTP_OK);
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
                'id'  => 'required|integer',
                'nome'  => 'required|string|max:45',
                'codigo'  => 'required|string|max:45',
                'nif'  => 'required|string|max:45|unique:entidades,nif,'. $request->id . 'null',
                'activo'  => 'required|integer',
                'endereco'  => 'required|string|max:45',
                'email'  => 'required|email|max:45|unique:entidades,email,'. $request->id . 'null',
                'telefone'  => 'required|string|max:45|unique:entidades,telefone,'. $request->id . 'null',
                'entidadeTipoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $entidade = Entidade::find($request->id);
            if($entidade == null) {
                return response()->json(['message' => 'Entidade não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $entidade->nome = $request->nome;
            $entidade->codigo = $request->codigo;
            $entidade->nif = $request->nif;
            $entidade->activo = $request->activo;
            $entidade->endereco = $request->endereco;
            $entidade->email = $request->email;
            $entidade->telefone = $request->telefone;
            $entidade->entidadeTipoId = $request->entidadeTipoId;
            $entidade->save();

            if($request->accesso){
                $utilizador = $this->updateUser($request, $entidade);

                return response()->json($utilizador);

                if($utilizador!=null){
                    $entidade->utilizadorId = $utilizador->id;
                    $entidade->save();
                }
            }

            return response()->json($entidade)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUser(Request $request, $entidade){

        $id = $entidade->utilizadorId;

        if($id==null){
            return $this->storeUser($request, $entidade);
        }else{
           
            $username = env('PORTAL_ENTIDADE_USERNAME');
            $password = env('PORTAL_ENTIDADE_PASSWORD');
    
            $credentials = $username . ':' . $password;
            $encodedCredentials = base64_encode($credentials);
    
            $body = [
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
                'nome' => $entidade->nome,
                'entidade' => $entidade->id,
                'activo' => $entidade->activo
            ];
    
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $encodedCredentials,
            ])->put('http://localhost:8001/admin/users/'.$id, $body);
        
            return $response->object();

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
            $entidade = Entidade::findOrFail($id);

            if ($entidade == null) {    
                return response()->json(['message' => 'Entidade não encontrada'], Response::HTTP_NOT_FOUND);
            }
            
            $entidade->delete();
            return response()->json(['message' => 'Entidade excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
