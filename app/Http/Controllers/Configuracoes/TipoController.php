<?php

namespace App\Http\Controllers\Configuracoes;

use App\Models\Tipo;
use App\Models\TipoTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\AtributoDinamico;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;


class TipoController extends Controller
{
    public function index()
    {
        try {

            $tipos = Tipo::with('atributosDinamicos', 'tipoTemplates.template')->where('id', '>', 0);

            if (request('nome') && !isNullOrEmpty(request('nome'))) {
                $tipos->where('nome', 'like', '%' . request('nome') . '%');
            }

            if (request('activo') && !isNullOrEmpty(request('activo'))) {
                $tipos->where('activo', 'like', '%' . request('activo') . '%');
            }

            if (request('atributosDinamico') && !isNullOrEmpty(request('atributosDinamico'))) {
                $tipos->where('atributosDinamico', 'like', '%' . request('atributosDinamico') . '%');
            }

            if (request('type') && !isNullOrEmpty(request('type'))) {
                $tipos->where('type', 'like', '%' . request('type') . '%');
            }

            if (request('confidencialidade') && !isNullOrEmpty(request('confidencialidade'))) {
                $tipos->where('confidencialidade', 'like', '%' . request('confidencialidade') . '%');
            }

            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {
                $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                $tipos->whereBetween('created_at', [$from, $to]);
            }

            if (request('order') && !isNullOrEmpty(request('order'))) {
                if (request('sort') && !isNullOrEmpty(request('sort'))) {
                    $tipos->orderBy(request('sort'), request('order'));
                }
            }

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $tipos->where(request('column'), 'like', '%' . request('value') . '%');
            }

            if (isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $tipos = $tipos->paginate($tipos->count(), ['*'], 'page', 1);

                return response()->json(repage($tipos))->setStatusCode(Response::HTTP_OK);
            }

            $tipos = $tipos->paginate(request('size'), ['*'], 'page', request('page') + 1);

            return response()->json(repage($tipos))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            // return  response()->json(['data'=> $request]);
            $validator = Validator::make($request->all(), [
                'nome'  => 'required|string|max:100',
                'descricao'  => 'required|string|max:300',
                'activo'  => 'required|boolean',
                'atributosDinamico'  => 'required|boolean',
                'type'  => 'required|string|max:45',
                'confidencialidade'  => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            if ($request->atributosDinamico) {
                $validator2 = Validator::make($request->all(), [
                    'tipoAtributos'  => 'required',
                ]);

                if ($validator2->fails()) {
                    return response()->json([$validator2->errors()->all()], Response::HTTP_BAD_REQUEST);
                }
            }

            $tipo = new Tipo();

            $insertTipo = $tipo->create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'activo' => $request->activo,
                'atributosDinamico' => $request->atributosDinamico,
                'type' => $request->type,
                'confidencialidade' => $request->confidencialidade,
            ]);

            /**
             * Criação dos directorios para armazenamento dos arquivos por tipo e ano
             */
            /*if ($request->type == 'DOC') {
                $filePath = 'app/uploads/registos/documentos/' . $request->nome;
            } else {
                $filePath = 'app/uploads/registos/processos/' . $request->nome;
            }

            if (!File::exists(storage_path($filePath))) {
                File::makeDirectory(storage_path($filePath));
            }*/

            if (!isset($insertTipo->id)) {
                return  response()->json(['message' => 'Erro ao cadastar o tipo '], Response::HTTP_BAD_REQUEST);
            }

            if ($request->atributosDinamico) {
                foreach ($request->tipoAtributos as $atributo) {
                    $atributoDinamico = new AtributoDinamico();
                    $atributoDinamico->key          = $atributo['key'];
                    $atributoDinamico->value        = $atributo['value'];
                    $atributoDinamico->label        = $atributo['label'];
                    $atributoDinamico->required     = $atributo['required'];
                    $atributoDinamico->type         = $atributo['type'];
                    $atributoDinamico->order        = $atributo['order'];
                    $atributoDinamico->controlType  = $atributo['controlType'];
                    $atributoDinamico->options      = $atributo['options'];
                    $atributoDinamico->tipoId       = $insertTipo->id;
                    $atributoDinamico->save();
                }
            }

            if(isset($request->templateId)){
                foreach ($request->templateId as $template) {
                    $tipoTemplate = new TipoTemplate();
                    $tipoTemplate->templateId  = $template;
                    $tipoTemplate->tipoId   = $insertTipo->id;
                    $tipoTemplate->save();
                }
            }

            $path = 'uploads/registos/documentos/' . $tipo->nome;

            // Make documento diretory
            if (!file_exists(storage_path('app/' . $path))) {
                mkdir(storage_path('app/' . $path), 0777, true);
            }
          
            return response()->json($insertTipo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            $tipo = Tipo::with('atributosDinamicos', 'tipoTemplates')->find($id);

            if ($tipo == null) {
                return response()->json(['message' => 'Tipo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $tipo], Response::HTTP_OK);
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
                'id'  => 'required|integer',
                'nome'  => 'required|string|max:100',
                'descricao'  => 'required|string|max:300',
                'activo'  => 'required|boolean',
                'atributosDinamico'  => 'required',
                'type'  => 'required|string|max:45',
                'confidencialidade'  => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            if ($request->atributosDinamico!=[] && $request->atributosDinamico!=null && $request->atributosDinamico!=0) {
                $validator2 = Validator::make($request->all(), [
                    'tipoAtributos'  => 'required',
                ]);

                if ($validator2->fails()) {
                    return response()->json([$validator2->errors()->all()], Response::HTTP_BAD_REQUEST);
                }
            }


            $tipo = Tipo::find($request->id);
            if ($tipo == null) {
                return response()->json(['message' => 'Tipo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $tipo->nome              = $request->nome;
            $tipo->descricao         = $request->descricao;
            $tipo->activo            = $request->activo;
            $tipo->atributosDinamico = $request->atributosDinamico;
            $tipo->type              = $request->type;
            $tipo->confidencialidade = $request->confidencialidade;
            $tipo->save();

            $atributoIds = [];

            if ($request->atributosDinamico!=[] && $request->atributosDinamico!=null && $request->atributosDinamico!=0) {
                foreach ($request->tipoAtributos as $atributo) {
                    if ($atributo['id'] != null) {
                        $atributoDinamico = AtributoDinamico::find($atributo['id']);
                        $atributoDinamico->key          = $atributo['key'];
                        $atributoDinamico->value        = $atributo['value'];
                        $atributoDinamico->label        = $atributo['label'];
                        $atributoDinamico->required     = $atributo['required'];
                        $atributoDinamico->type         = $atributo['type'];
                        $atributoDinamico->order        = $atributo['order'];
                        $atributoDinamico->controlType  = $atributo['controlType'];
                        $atributoDinamico->options      = $atributo['options'];
                        $atributoDinamico->save();
                    } else {
                        $atributoDinamico = new AtributoDinamico();

                        $atributoDinamico->key          = $atributo['key'];
                        $atributoDinamico->value        = $atributo['value'];
                        $atributoDinamico->label        = $atributo['label'];
                        $atributoDinamico->required     = $atributo['required'];
                        $atributoDinamico->type         = $atributo['type'];
                        $atributoDinamico->order        = $atributo['order'];
                        $atributoDinamico->controlType  = $atributo['controlType'];
                        $atributoDinamico->options      = $atributo['options'];
                        $atributoDinamico->tipoId       = $tipo->id;
                        $atributoDinamico->save();
                    }
                    $atributoIds[] = $atributoDinamico->id; 
                }
            }

            if(isset($request->templateId)){
                TipoTemplate::where('tipoId',  $tipo->id)->delete();

                foreach ($request->templateId as $template) {
                    $tipoTemplate = new TipoTemplate();
                    $tipoTemplate->templateId  = $template;
                    $tipoTemplate->tipoId   = $tipo->id;
                    $tipoTemplate->save();
                }
            }

            // Delete removed atributes
            AtributoDinamico::where('tipoId',  $tipo->id)->whereNotIn('id', $atributoIds)->delete();

            $path = 'uploads/registos/documentos/' . $tipo->nome;

            // Make documento diretory
            if (!file_exists(storage_path('app/' . $path))) {
                mkdir(storage_path('app/' . $path), 0777, true);
            }

            return response()->json($tipo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador ' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $tipo = Tipo::findOrFail($id);

            if ($tipo == null) {
                return response()->json(['message' => 'Tipo não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $tipo->delete();
            return response()->json(['message' => 'Tipo excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
