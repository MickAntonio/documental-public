<?php

namespace App\Http\Controllers\Registos;

use App\Models\Tipo;
use App\Models\User;
use App\Models\Anexo;
use App\Models\Estado;
use App\Models\Registo;
use App\Models\Entidade;
use App\Models\Template;
use App\Models\Documento;
use Illuminate\Http\Request;
use App\Models\AnexoPendente;
use Illuminate\Http\Response;
use App\Models\RegistoRemetente;
use App\Models\DocumentoAssociado;
use Illuminate\Support\Facades\DB;
use App\Models\RegistoDestinatario;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ValorAtributoDinamico;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Events\AuditCustom;
use Illuminate\Support\Facades\Validator;

class RegistoController extends Controller
{
    public function index()
    {
        try {

            $userGroups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');

            $registos = Registo::with([
                'estado',
                'tipo',
                'documento',
                'utilizador',
                'anexos.utilizador',
                'anexosHistorico.utilizador',
                'registoRemetente.entidade',
                'registoRemetente.utilizador',
                'registoDestinatario.entidade',
                'registoDestinatario.utilizador',
                'registoPermissoes.entidade',
                'registoPermissoes.utilizador',
                'valorAtributoDinamicos.atributo',
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.utilizador',
                'encaminhamentos.destinatarios' => function ($query) use ($userGroups) {
                    $query->when('destinatarios', function($q) use ($userGroups) {
                        $q->where('utilizadorId',  Auth::user()->id)->orWhereIn('grupoId', $userGroups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        });
                    });
                },
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.utilizador.utilizadoresInterinos'
            ])
            ->where('id', '>', 0);

            if (request('referencia') && !isNullOrEmpty(request('referencia'))) {
                $registos->where('referencia', 'like', '%' . request('referencia') . '%');
            }

            if (request('pesquisaPor') && !isNullOrEmpty(request('pesquisaPor'))) {
                $registos->whereHas('documento', function ($query) {
                    $query->where('numeroDocumento', 'like', '%' . request('pesquisaPor') . '%');;
                })->orWhere('assunto', 'like', '%' . request('pesquisaPor') . '%');
            }

            if (request('numeroDocumento') && !isNullOrEmpty(request('numeroDocumento'))) {
                $registos->whereHas('documento', function ($query) {
                    $query->where('numeroDocumento', 'like', '%' . request('numeroDocumento') . '%');;
                });
            }

            if (request('protocolo') && !isNullOrEmpty(request('protocolo'))) {
                $registos->whereHas('protocolo', function ($query) {
                    $query->where('protocolo', 'like', '%' . request('protocolo') . '%');;
                });
            }

            if (request('dataDocumento') && !isNullOrEmpty(request('dataDocumento'))) {
                $registos->where('data',  date_format(date_create(request('dataDocumento')), 'Y-m-d'));
            }

            if (request('assunto') && !isNullOrEmpty(request('assunto'))) {
                $registos->where('assunto', 'like', '%' . request('assunto') . '%');
            }

            if (request('tipoId') && !isNullOrEmpty(request('tipoId'))) {
                $registos->where('tipoId', request('tipoId'));
            }

            if (request('pendentes') && !isNullOrEmpty(request('pendentes'))) {

                $registos = $registos->whereHas('encaminhamentos', function ($encaminhamento) {
                    $encaminhamento->when('obrigatorioTodos' == true, function ($query) {
                        $query->whereHas('destinatarios', function ($destinatario) {
                            $groups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');
                            $destinatario->where('pendente', true)
                                ->where('utilizadorId', Auth::user()->id)
                                ->orWhereIn('grupoId', $groups)
                                ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                                    $query->where('utilizadorInterinoId', Auth::user()->id);
                                })
                                ->where('pendente', true);
                        })->where('pendente', true);
                    }, function ($query) {
                        $query->whereHas('destinatarios', function ($destinatario) {
                            $groups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');
                            $destinatario->where('utilizadorId', Auth::user()->id)
                            ->orWhereIn('grupoId', $groups)
                            ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                                $query->where('utilizadorInterinoId', Auth::user()->id);
                            });
                        })->where('pendente', true);
                    });
                });
            }

            if (isNullOrEmpty(request('pendentes'))) {
                $registos->when('confidencial' == true, function($query){
                    $query->where('confidencial', false)->orWhere('utilizadorId', Auth::user()->id)
                    ->orWhereHas('registoPermissoes', function ($registoPermissoes) {
                        $groups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');
                        $registoPermissoes->where('utilizadorId', Auth::user()->id)
                            ->orWhereIn('grupoId', $groups);
                    });
                });
            }

            if (request('participanteId') && !isNullOrEmpty(request('participanteId'))) {

                $participante = explode('#', request('participanteId'));

                $id   = $participante[0];
                $tipo = $participante[1];

                if($tipo=='UTILIZADOR'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'UTILIZADOR')->where('utilizadorId', $id);
                    })->orWhereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'UTILIZADOR')->where('utilizadorId', $id);
                    });
                }else if($tipo=='GRUPO'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'GRUPO')->where('grupoId', $id);
                    })->orWhereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'GRUPO')->where('grupoId', $id);
                    });
                }else if($tipo=='ENTIDADE'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'ENTIDADE')->where('entidadeId', $id);
                    })->orWhereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'ENTIDADE')->where('entidadeId', $id);
                    });
                }

                if (request('pendentes') && !isNullOrEmpty(request('pendentes'))) {
                    $registos->whereHas('encaminhamentos', function ($query) {
                        $query->where('pendente', true);
                    });
                }
            }

            if (request('remetenteId') && !isNullOrEmpty(request('remetenteId'))) {

                $remitente = explode('#', request('remetenteId'));

                $id   = $remitente[0];
                $tipo = $remitente[1];

                if($tipo=='UTILIZADOR'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'UTILIZADOR')->where('utilizadorId', $id);
                    });
                }else if($tipo=='GRUPO'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'GRUPO')->where('grupoId', $id);
                    });
                }else if($tipo=='ENTIDADE'){
                    $registos->whereHas('registoRemetente', function ($query) use ($id) {
                        $query->where('tipo', 'ENTIDADE')->where('entidadeId', $id);
                    });
                }
            }

            if (request('destinatarioId') && !isNullOrEmpty(request('destinatarioId'))) {

                $destinatario = explode('#', request('destinatarioId'));

                $id   = $destinatario[0];
                $tipo = $destinatario[1];

                if($tipo=='UTILIZADOR'){
                    $registos->whereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'UTILIZADOR')->where('utilizadorId', $id);
                    });
                }else if($tipo=='GRUPO'){
                    $registos->whereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'GRUPO')->where('grupoId', $id);
                    });
                }else if($tipo=='ENTIDADE'){
                    $registos->whereHas('registoDestinatario', function ($query) use ($id) {
                        $query->where('tipo', 'ENTIDADE')->where('entidadeId', $id);
                    });
                }
            }

            if (request('confidencial') && !isNullOrEmpty(request('confidencial'))) {
                $registos->where('confidencial', 'like', '%' . request('confidencial') . '%');
            }

            if (request('criadoPor') && !isNullOrEmpty(request('criadoPor'))) {
                $registos->where('criadoPor', 'like', '%' . request('criadoPor') . '%');
            }

            if (request('estadoId') && !isNullOrEmpty(request('estadoId'))) {
                $registos->where('estadoId', request('estadoId'));
            }

            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {

                if(request('dtInitial')==request('dtEnd')){
                    $registos->where('data',  date_format(date_create(request('dtInitial')), 'Y-m-d'));
                }else{
                    $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                    $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                    $registos->whereBetween('data', [$from, $to]);
                }
            }else if(request('dtInitial') && !isNullOrEmpty(request('dtInitial'))){
                $registos->where('data', '>=',  date_format(date_create(request('dtInitial')), 'Y-m-d'));
            }else if(request('dtEnd') && !isNullOrEmpty(request('dtEnd'))){
                $registos->where('data', '<=',  date_format(date_create(request('dtEnd')), 'Y-m-d'));
            }

            
            if (request('dtRegistoInitial') && !isNullOrEmpty(request('dtRegistoInitial')) && request('dtRegistoFinal') && !isNullOrEmpty(request('dtRegistoFinal'))) {

                if(request('dtRegistoInitial')==request('dtRegistoFinal')){
                    $registos->where('created_at',  date_format(date_create(request('dtRegistoInitial')), 'Y-m-d'));
                }else{
                    $from = date_format(date_create(request('dtRegistoInitial') . '00:00:00'), 'Y-m-d H:i:s');
                    $to = date_format(date_create(request('dtRegistoFinal') . '23:59:59'), 'Y-m-d H:i:s');
                    $registos->whereBetween('created_at', [$from, $to]);
                }
            }else if(request('dtRegistoInitial') && !isNullOrEmpty(request('dtRegistoInitial'))){
                $registos->where('created_at', '>=',  date_format(date_create(request('dtRegistoInitial')), 'Y-m-d'));
            }else if(request('dtRegistoFinal') && !isNullOrEmpty(request('dtRegistoFinal'))){
                $registos->where('created_at', '<=',  date_format(date_create(request('dtRegistoFinal')), 'Y-m-d'));
            }

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $registos->where(request('column'), 'like', '%' . request('value') . '%');
            }

           
            /*if (request('order') && !isNullOrEmpty(request('order'))) {
                if (request('sort') && !isNullOrEmpty(request('sort'))) {

                    if(request(('sort')!='numeroDocumento')){
                        $registos->orderBy(request('sort'), request('order'));
                    }else{
                        $registos->whereHas('documento', function($query){
                            $query->orderBy(request('sort'), request('order'));
                        });
                    }
                }
            }*/

            if (isNullOrEmpty(request('sort')) && isNullOrEmpty(request('order'))) {
                $registos->orderBy('id', 'desc');
            }else{
                $registos->orderBy(request('sort'), request('order'));
            }

            if (isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $registos = $registos->paginate($registos->count(), ['*'], 'page', 1);

                return response()->json(repage($registos))->setStatusCode(Response::HTTP_OK);
            }

            $registos = $registos->paginate(request('size'), ['*'], 'page', request('page') + 1);

            return response()->json(repage($registos))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function totalPendentes()
    {
        try {

            $userGroups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');

            $registos = Registo::with([
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.destinatarios' => function ($query) use ($userGroups) {
                    $query->when('destinatarios', function($q) use ($userGroups) {
                        $q->where('utilizadorId',  Auth::user()->id)->orWhereIn('grupoId', $userGroups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        });
                    });
                },
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.utilizador.utilizadoresInterinos'
            ])
            ->whereHas('encaminhamentos', function ($encaminhamento) {
                $encaminhamento->when('obrigatorioTodos' == true, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {
                        $groups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');
                        $destinatario->where('pendente', true)->where('utilizadorId', Auth::user()->id)->orWhereIn('grupoId', $groups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        })
                        ->where('pendente', true);
                    })->where('pendente', true);
                }, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {
                        $groups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');
                        $destinatario->where('utilizadorId', Auth::user()->id)->orWhereIn('grupoId', $groups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        });
                    })->where('pendente', true);
                });
            });


            $pendentesPorClassificar = AnexoPendente::where('classificado', false)->count();

            $totalPendentes = ['pendentes'=>$registos->count(), 'pendentesPorClassificar'=>$pendentesPorClassificar];

            return response()->json($totalPendentes)->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'assunto'  => 'required|string|max:300',
                'tipoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            if (isset($request->estado)) {
                $estado = $request->estado;
            } else {
                $estado = 'NOVO';
            }

            $estado = Estado::where("nome", $estado)->first();
            if (!isset($estado)) {
                return response()->json(['message' => "Estado $estado não existe"], Response::HTTP_BAD_REQUEST);
            }

            $registo = new Registo();
            $registo->assunto            = $request->assunto;
            $registo->criadoPor          = $request->criadoPor;
            //$registo->localizacaoFisica  = $request->localizacaoFisica;
            $registo->data               = date('Y-m-d', strtotime($request->data));
            $registo->confidencial       = $request->confidencial;
            $registo->estadoId           = $estado->id;
            $registo->tipoId             = $request->tipoId;
            $registo->utilizadorId       = $request->userId;
            $registo->registoTipo        = $request->registoTipo;
            if(isset($request->protocolo)){
                $registo->protocolo = $request->protocolo;
            }
            $registo->save();

            $documento = new Documento();
            $documento->numeroDocumento = sequenceNextByCode('DOC');
            $documento->registoId       = $registo->id;
            $documento->save();

            //Referência 
            $registo->referencia         = $documento->numeroDocumento;
            $registo->save();

            // Cadastro de remetentes
            $tipoRemetente = $request->registoRemetente["tipo"];
            $registoRemetente = new RegistoRemetente();
            $registoRemetente->tipo      = $tipoRemetente;
            $registoRemetente->registoId = $registo->id;

            if ($tipoRemetente == "UTILIZADOR") {
                $registoRemetente->utilizadorId  = $request->registoRemetente["utilizadorId"];
            } else if ($tipoRemetente == "ENTIDADE") {
                $registoRemetente->entidadeId  = $request->registoRemetente["entidadeId"];
            } else if ($tipoRemetente == "GRUPO") {
                $registoRemetente->grupoId  = $request->registoRemetente["grupoId"];
            }

            $registoRemetente->save();

            // Cadastro de destinatários
            foreach ($request->registoDestinatario as $registo_destinatario) {
                $registoDestinatario = new RegistoDestinatario();
                $tipoDestinatario = $registo_destinatario["tipo"];
                if ($tipoDestinatario == "UTILIZADOR") {
                    $registoDestinatario->utilizadorId  = $registo_destinatario["utilizadorId"];
                } else if ($tipoDestinatario == "ENTIDADE") {
                    $registoDestinatario->entidadeId  = $registo_destinatario["entidadeId"];
                } else if ($tipoDestinatario == "GRUPO") {
                    $registoDestinatario->grupoId  = $registo_destinatario["grupoId"];
                }

                $registoDestinatario->tipo         = $tipoDestinatario;
                $registoDestinatario->registoId    = $registo->id;
                $registoDestinatario->save();
            }

            if (is_array($request->anexos)) {

                $tipo = Tipo::find($request->tipoId);
                // Path to save the file
                $path = 'uploads/registos/documentos/' . $tipo->nome . '/' . date('Y') . '/' . explode('/', $documento->numeroDocumento)[1];

                // Make documento diretory
                if (!file_exists(storage_path('app/' . $path))) {
                    mkdir(storage_path('app/' . $path), 0777, true);
                }

                // Save all anexos
                foreach ($request->anexos as $anexoRequest) {

                    // Decode the base64 data
                    $base64 = explode(',', $anexoRequest['file'])[1];
                    $decodedData = base64_decode($base64);

                    // File name
                    $fileName = $anexoRequest['name'];

                    // Create a new file with name and extension
                    $filePath = $path . '/' . $fileName . $anexoRequest['extension'];

                    if (file_exists(storage_path('app/' . $filePath))) {
                        $filePath = $path . '/' . $fileName . uniqid() . $anexoRequest['extension'];
                    }

                    // Write the decoded data to the new file
                    file_put_contents(storage_path('app/' . $filePath), $decodedData);

                    $anexo = new Anexo();
                    $anexo->nome        = $fileName;
                    $anexo->tamanho     = $anexoRequest['size'];
                    $anexo->extensao    = $anexoRequest['extension'];
                    $anexo->versao      = 1.0;
                    $anexo->estado      = 'IN';
                    $anexo->criadoPor   = $request->username;
                    $anexo->editadoPor  = $request->username;
                    $anexo->localizacao = $filePath;
                    $anexo->registoId   = $registo->id;;
                    $anexo->save();
                }
            }

            if (is_array($request->anexosScanner)) {

                $tipo = Tipo::find($request->tipoId);
                // Path to save the file
                $path = 'uploads/registos/documentos/' . $tipo->nome . '/' . date('Y') . '/' . explode('/', $documento->numeroDocumento)[1];

                // Make documento diretory
                if (!file_exists(storage_path('app/' . $path))) {
                    mkdir(storage_path('app/' . $path), 0777, true);
                }

                $anexosPendentes = AnexoPendente::whereIn('id', $request->anexosScanner)->get();

                // Save all anexos
                foreach ($anexosPendentes as $anexoPendente) {

                    // File name
                    $fileName = $anexoPendente->nome;

                    // Create a new file with name and extension
                    $filePath = $path . '/' . $fileName . $anexoPendente->extensao;


                    if (file_exists(storage_path('app/' . $filePath))) {
                        $filePath = $path . '/' . $fileName . uniqid() . $anexoPendente->extensao;
                    }

                    // Write the decoded data to the new file
                    // Copy pendente file to registo file
                    copy(storage_path('app/' . $anexoPendente->localizacao), storage_path('app/' . $filePath));

                    $anexo = new Anexo();
                    $anexo->nome        = $fileName;
                    $anexo->tamanho     = $anexoPendente->tamanho;
                    $anexo->extensao    = $anexoPendente->extensao;
                    $anexo->versao      = 1.0;
                    $anexo->estado      = 'IN';
                    $anexo->criadoPor   = $request->username;
                    $anexo->editadoPor  = $request->username;
                    $anexo->localizacao = $filePath;
                    $anexo->registoId   = $registo->id;;
                    $anexo->save();

                    $anexoPendente->classificado = true;
                    $anexoPendente->save();
                }
            }

            if(($request->registoTipo!='INTERNO' || $request->registoTipo!='SAIDA') && $request->templateId>0){

                $template = Template::find($request->templateId);

                if($template!=null){

                    $tipo = Tipo::find($request->tipoId);
                    // Path to save the file
                    $path = 'uploads/registos/documentos/' . $tipo->nome . '/' . date('Y') . '/' . explode('/', $documento->numeroDocumento)[1];

                    // Make documento diretory
                    if (!file_exists(storage_path('app/' . $path))) {
                        mkdir(storage_path('app/' . $path), 0777, true);
                    }

                    // Decode the base64 data
                    $extensionSupose = explode(',', $template->template)[0];

                    $extension = '.docx';

                    if($extensionSupose=='data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64'){
                        $extension = '.docx';
                    }else if($extensionSupose=='data:application/pdf;base64'){
                        $extension = '.pdf';
                    }else if($extensionSupose=='data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64'){
                        $extension = '.xlsx';
                    }else if($extensionSupose=='data:application/vnd.openxmlformats-officedocument.presentationml.presentation;base64'){
                        $extension = '.pptx';
                    }

                    $base64 = explode(',', $template->template)[1];
                    $decodedData = base64_decode($base64);

                    $sizeInBytes = strlen($decodedData);

                    // File name
                    $fileName = $template->nome;

                    // Create a new file with name and extension
                    $filePath = $path . '/' . $fileName . $extension;

                    if (file_exists(storage_path('app/' . $filePath))) {
                        $filePath = $path . '/' . $fileName . uniqid() . $extension;
                    }

                    // Write the decoded data to the new file
                    file_put_contents(storage_path('app/' . $filePath), $decodedData);

                    $anexo = new Anexo();
                    $anexo->nome        = $template->nome;
                    $anexo->tamanho     = round($sizeInBytes, 2);
                    $anexo->extensao    = $extension;
                    $anexo->versao      = 1.0;
                    $anexo->estado      = 'IN';
                    $anexo->criadoPor   = $request->username;
                    $anexo->editadoPor  = $request->username;
                    $anexo->localizacao = $filePath;
                    $anexo->registoId   = $registo->id;;
                    $anexo->save();

                }
                
            }

            if($request->valorAtributoDinamicos!=null){
                foreach ($request->valorAtributoDinamicos as $key => $atributo) {
                    $valorAtributo = new ValorAtributoDinamico();
                    $valorAtributo->valor              = $atributo;
                    $valorAtributo->atributoDinamicoId = $key;
                    $valorAtributo->registoId   = $registo->id;;
                    $valorAtributo->save();
                }
            }
            
            DB::commit();

            return response()->json($registo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollback();

            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $registo = Registo::find($id);

            $registo->auditEvent = 'saw';
            $registo->isCustomEvent = true;
           
            Event::dispatch(AuditCustom::class, [$registo]);

            if ($registo == null) {
                return response()->json(['message' => 'Registro não encontrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $registo], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * RETORNA TODOS OS DADOS DE UM DOCUMENTO
     **/
    public function showDetailDocumentById($id)
    {
        try {

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $userGroups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');

            $registo = Registo::with([
                'estado',
                'tipo',
                'documento',
                'utilizador',
                'anexos.utilizador',
                'anexosHistorico.utilizador',
                'registoRemetente.entidade',
                'registoRemetente.utilizador',
                'registoDestinatario.entidade',
                'registoDestinatario.utilizador',
                'valorAtributoDinamicos.atributo',
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.utilizador',
                'encaminhamentos.destinatarios' => function ($query) use ($userGroups) {
                    $query->when('destinatarios', function($q) use ($userGroups) {
                        $q->where('utilizadorId',  Auth::user()->id)->orWhereIn('grupoId', $userGroups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        });
                    })->where('pendente', true);
                },
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.utilizador.utilizadoresInterinos'
            ])->find($id);

            if ($registo == null) {
                return response()->json(['message' => 'Registro não encotrada'], Response::HTTP_NOT_FOUND);
            }

            // custom audit
            $registo->auditEvent = 'saw';
            $registo->isCustomEvent = true;
            Event::dispatch(AuditCustom::class, [$registo]);

            return response()->json(['data' => $registo], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * RETORNA TODOS OS DADOS DE UM DOCUMENTO
     **/
    public function showDetailDocumentByIdRefresh($id)
    {
        try {

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $userGroups = collect(KeycloakClient()->getUserGroups(['id' => Auth::user()->id]))->pluck('id');

            $registo = Registo::with([
                'estado',
                'tipo',
                'documento',
                'utilizador',
                'anexos.utilizador',
                'anexosHistorico.utilizador',
                'registoRemetente.entidade',
                'registoRemetente.utilizador',
                'registoDestinatario.entidade',
                'registoDestinatario.utilizador',
                'valorAtributoDinamicos.atributo',
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.utilizador',
                'encaminhamentos.destinatarios' => function ($query) use ($userGroups) {
                    $query->when('destinatarios', function($q) use ($userGroups) {
                        $q->where('utilizadorId',  Auth::user()->id)->orWhereIn('grupoId', $userGroups)
                        ->orwhereHas('utilizador.utilizadoresInterinos', function($query){
                            $query->where('utilizadorInterinoId', Auth::user()->id);
                        });
                    })->where('pendente', true);
                },
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.utilizador.utilizadoresInterinos'
            ])->find($id);

            if ($registo == null) {
                return response()->json(['message' => 'Registro não encotrada'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['data' => $registo], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * AuditEvent custom audit
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function auditSawEvent(Request $request)
    {
        $registo = Registo::find($request->id);

        if ($registo == null) {
            return response()->json(['message' => 'Registro não encotrada'], Response::HTTP_NOT_FOUND);
        }
        
        $registo->auditEvent = 'saw';
        $registo->isCustomEvent = true;
        Event::dispatch(AuditCustom::class, [$registo]);

        return response()->json(['message'=>'Audited'], Response::HTTP_OK);
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

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'id'  => 'required|integer',
                'assunto'  => 'required|string|max:300',
                'tipoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $registo = Registo::find($request->id);

            if ($registo == null) {
                return response()->json(['message' => 'Registo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            if (isset($request->estado)) {
                $estado = $request->estado;
            } else {
                $estado = 'NOVO';
            }

            $estado = Estado::where("nome", $estado)->first();
            if (!isset($estado)) {
                return response()->json(['message' => "Estado $estado não existe"], Response::HTTP_BAD_REQUEST);
            }

            $registo->assunto            = $request->assunto;
            $registo->criadoPor          = $request->criadoPor;
            //$registo->localizacaoFisica  = $request->localizacaoFisica;
            $registo->data               = date('Y-m-d', strtotime($request->data));
            $registo->confidencial       = $request->confidencial;
            $registo->estadoId           = $estado->id;
            $registo->tipoId             = $request->tipoId;
            $registo->utilizadorId       = $request->userId;
            $registo->registoTipo        = $request->registoTipo;
            if(isset($request->protocolo)){
                $registo->protocolo = $request->protocolo;
            }
            $registo->save();

            // Documento
            $documento = Documento::where('registoId', $request->id)->first();

            // Cadastro de remetente
            $tipoRemetente = $request->registoRemetente["tipo"];

            $registoRemetenteId = $this->getIdTipos($tipoRemetente);

            $registoRemetente = RegistoRemetente::where('registoId', $registo->id)->first();

            if ($registoRemetente == null) {
                $registoRemetente = new RegistoRemetente;
            }

            $registoRemetente->tipo      = $tipoRemetente;
            $registoRemetente->registoId = $registo->id;
            $registoRemetente[$registoRemetenteId]  = $request->registoRemetente[$registoRemetenteId];
            $registoRemetente->save();


            // Cadastro de destinatários
            $registoDestinatarioIds = [];
            foreach ($request->registoDestinatario as $registo_destinatario) {

                $tipoDestinatario = $registo_destinatario["tipo"];

                $tipoId = $this->getIdTipos($tipoDestinatario);

                $registoDestinatario = RegistoDestinatario::where('registoId', $registo->id)->where('tipo', $tipoDestinatario)->where($tipoId, $registo_destinatario[$tipoId])->first();

                if ($registoDestinatario == null) {
                    $registoDestinatario = new RegistoDestinatario();
                }

                $registoDestinatario[$tipoId]  = $registo_destinatario[$tipoId];
                $registoDestinatario->tipo         = $tipoDestinatario;
                $registoDestinatario->registoId    = $registo->id;
                $registoDestinatario->save();

                $registoDestinatarioIds[] = $registoDestinatario->id;
            }

            // Excluir todos os destinatarios removidos
            RegistoDestinatario::where('registoId', $registo->id)->whereNotIn('id', $registoDestinatarioIds)->delete();

            if (is_array($request->anexos)) {

                $tipo = Tipo::find($request->tipoId);
                // Path to save the file
                $path = 'uploads/registos/documentos/' . $tipo->nome . '/' . date('Y') . '/' . explode('/', $documento->numeroDocumento)[1];

                // Make documento diretory
                if (!file_exists(storage_path('app/' . $path))) {
                    mkdir(storage_path('app/' . $path), 0777, true);
                }

                // Save all anexos
                foreach ($request->anexos as $anexoRequest) {

                    // Decode the base64 data
                    $base64 = explode(',', $anexoRequest['file'])[1];
                    $decodedData = base64_decode($base64);

                    // File name
                    $fileName = $anexoRequest['name'];

                    // Create a new file with name and extension
                    $filePath = $path . '/' . $fileName . $anexoRequest['extension'];

                    if (file_exists(storage_path('app/' . $filePath))) {
                        $filePath = $path . '/' . $fileName . uniqid() . $anexoRequest['extension'];
                    }

                    // Write the decoded data to the new file
                    file_put_contents(storage_path('app/' . $filePath), $decodedData);

                    $anexo = new Anexo();
                    $anexo->nome        = $fileName;
                    $anexo->tamanho     = $anexoRequest['size'];
                    $anexo->extensao    = $anexoRequest['extension'];
                    $anexo->versao      = 1.0;
                    $anexo->estado      = 'IN';
                    $anexo->criadoPor   = $request->username;
                    $anexo->editadoPor  = $request->username;
                    $anexo->localizacao = $filePath;
                    $anexo->registoId   = $registo->id;;
                    $anexo->save();
                }
            }

            // update valorAtributoDinamicos
            if($request->valorAtributoDinamicos!=null){
                $this->updateValorAtributoDinamico($request->valorAtributoDinamicos, $registo->id);
            }
            DB::commit();

            return response()->json($registo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollback();

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
    public function updateEstado(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'  => 'required|integer',
                'estadoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $registo = Registo::find($request->id);

            if ($registo == null) {
                return response()->json(['message' => 'Registo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $registo->estadoId           = $request->estadoId;
            $registo->save();

            return response()->json($registo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador ' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateValorAtributoDinamico(array $valorAtributoDinamicos, int $registoId): void
    {
        $updatedIds = [];

        foreach ($valorAtributoDinamicos as $key => $value) {
            $valorAtributo = ValorAtributoDinamico::firstOrNew([
                'registoId' => $registoId,
                'atributoDinamicoId' => $key,
            ]);

            if ($valorAtributo->valor !== $value) {
                $valorAtributo->valor = $value;
                $valorAtributo->save();
            }

            $updatedIds[] = $valorAtributo->id;
        }

        ValorAtributoDinamico::where('registoId', $registoId)
            ->whereNotIn('id', $updatedIds)
            ->delete();
    }


    public function getIdTipos($tipo)
    {
        if ($tipo == "UTILIZADOR") {
            $id = 'utilizadorId';
        } else if ($tipo == "ENTIDADE") {
            $id = 'entidadeId';
        } else if ($tipo == "GRUPO") {
            $id = 'grupoId';
        }

        return $id;
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            $registo = Registo::findOrFail($id);

            if ($registo == null) {
                return response()->json(['message' => 'Registo não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $registo->delete();
            return response()->json(['message' => 'Registo excluida'], Response::HTTP_NO_CONTENT);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PESQUISA AVANÇADO de Documento
     */
    public function seachAdvancedDocument()
    {
        try {
            $data = [];
            $registos = Registo::with('estado', 'empresa', 'tipo', 'documento', 'registoRemetente', 'registoDestinatario')->where('id', '>', 0);

            if (request('referencia') && !isNullOrEmpty(request('referencia'))) {
                $registos->where('referencia', 'like', '%' . request('referencia') . '%');
            }

            if (request('assunto') && !isNullOrEmpty(request('assunto'))) {
                $registos->where('assunto', 'like', '%' . request('assunto') . '%');
            }

            if (request('confidencial') && !isNullOrEmpty(request('confidencial'))) {
                $registos->where('confidencial', 'like', '%' . request('confidencial') . '%');
            }

            if (request('criadoPor') && !isNullOrEmpty(request('criadoPor'))) {
                $registos->where('criadoPor', 'like', '%' . request('criadoPor') . '%');
            }

            if (request('localizacaoFisica') && !isNullOrEmpty(request('localizacaoFisica'))) {
                $registos->where('localizacaoFisica', 'like', '%' . request('localizacaoFisica') . '%');
            }
            if (request('estadoId') && !isNullOrEmpty(request('estadoId'))) {
                $registos->where('estadoId', request('estadoId'));
            }
            if (request('tipoId') && !isNullOrEmpty(request('tipoId'))) {
                $registos->where('tipoId', request('tipoId'));
            }

            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {
                $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                $registos->whereBetween('created_at', [$from, $to]);
            }

            if (request('order') && !isNullOrEmpty(request('order'))) {
                if (request('sort') && !isNullOrEmpty(request('sort'))) {
                    $registos->orderBy(request('sort'), request('order'));
                }
            }

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $registos->where(request('column'), 'like', '%' . request('value') . '%');
            }

            if (isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $registos = $registos->paginate($registos->count(), ['*'], 'page', 1);

                return response()->json(repage($registos))->setStatusCode(Response::HTTP_OK);
            }

            $registos = $registos->paginate(request('size'), ['*'], 'page', request('page') + 1);

            return response()->json(repage($registos), Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador: ' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexParticipantes()
    {
        try {

            $participantes = [];

            $users     = User::all();
            $entidades = Entidade::all(['id', 'nome']);
            $groups    = KeycloakClient()->getGroups();

            foreach ($users as $user) {
                $participantes[] = ['id' => $user->id . '#UTILIZADOR', 'name' => $user->name];
            }

            foreach ($entidades as $entidade) {
                $participantes[] = ['id' => $entidade->id . '#ENTIDADE', 'name' => $entidade->nome];
            }

            foreach ($groups as $group) {
                $participantes[] = ['id' => $group['id'] . '#GRUPO', 'name' => $group['name']];
            }

            return response()->json(['data' => $participantes], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador: ' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
