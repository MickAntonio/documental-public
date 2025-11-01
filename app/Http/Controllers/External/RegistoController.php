<?php

namespace App\Http\Controllers\External;

use App\Models\Tipo;
use App\Models\Anexo;
use App\Models\Estado;
use App\Models\Registo;
use App\Models\Template;
use App\Models\Documento;
use Illuminate\Http\Request;
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

            if(!request('entidadeId') || isNullOrEmpty(request('entidadeId'))){
                return response()->json(['message' => "o Id da Entidade é obrigatório"], Response::HTTP_BAD_REQUEST);
            }
            
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
                'valorAtributoDinamicos.atributo',
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.utilizador',
                'encaminhamentos.destinatarios',
                'encaminhamentos.destinatarios.utilizador',
                'encaminhamentos.destinatarios.entidade'

            ])
            ->where('id', '>', 0);

          /*  if (request('referencia') && !isNullOrEmpty(request('referencia'))) {
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

            if (request('estadoId') && !isNullOrEmpty(request('estadoId'))) {
                $registos->where('estadoId', request('estadoId'));
            }

            if (request('pesquisaPor') && !isNullOrEmpty(request('pesquisaPor'))) {
                $registos->whereHas('documento', function ($query) {
                    $query->where('numeroDocumento', 'like', '%' . request('pesquisaPor') . '%');;
                })->orWhere('assunto', 'like', '%' . request('pesquisaPor') . '%');
            }

            if (request('dataDocumento') && !isNullOrEmpty(request('dataDocumento'))) {
                $registos->where('data',  date_format(date_create(request('dataDocumento')), 'Y-m-d'));
            }

            if (request('tipoId') && !isNullOrEmpty(request('tipoId'))) {
                $registos->where('tipoId', request('tipoId'));
            }


            if (request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && request('dtEnd') && !isNullOrEmpty(request('dtEnd'))) {
                $from = date_format(date_create(request('dtInitial') . '00:00:00'), 'Y-m-d H:i:s');
                $to = date_format(date_create(request('dtEnd') . '23:59:59'), 'Y-m-d H:i:s');
                $registos->whereBetween('created_at', [$from, $to]);
            }

            $registos = $registos->whereHas('encaminhamentos', function ($encaminhamento) {
                $encaminhamento->whereHas('destinatarios', function ($destinatario) {
                        $destinatario->where('entidadeId', request('entidadeId'));
                });
            });

            if (request('value') && request('column') && !isNullOrEmpty(request('value')) && !isNullOrEmpty(request('column'))) {
                $registos->where(request('column'), 'like', '%' . request('value') . '%');
            }

            if (request('pendentes') && !isNullOrEmpty(request('pendentes'))) {
                $registos = $registos->whereHas('encaminhamentos', function ($encaminhamento) {
                    $encaminhamento->when('obrigatorioTodos' == true, function ($query) {
                        $query->whereHas('destinatarios', function ($destinatario) {
                            $destinatario->where('pendente', true)->where('entidadeId', request('entidadeId'));
                        })->where('pendente', true);
                    }, function ($query) {
                        $query->whereHas('destinatarios', function ($destinatario) {
                            $destinatario->where('entidadeId', request('entidadeId'))->where('pendente', true);
                        })->where('pendente', true);
                    });
                });
            }*/
            

            if (request('order') && !isNullOrEmpty(request('order'))) {
                if (request('sort') && !isNullOrEmpty(request('sort'))) {

                    if(request(('sort')!='numeroDocumento')){
                        $registos->orderBy(request('sort'), request('order'));
                    }else{
                        $registos->whereHas('documento', function($query){
                            $query->orderBy(request('sort'), request('order'));
                        });
                    }
                }
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

    public function showDetailDocumentById($id)
    {
        try {

            $validator = Validator::make(['id' => $id, 'entidadeId'=> request('entidadeId')], [
                'id'  => 'required|integer',
                'entidadeId' =>  'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

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
                'encaminhamentos.destinatarios' => function ($query) {
                    $query->when('destinatarios', function($q) {
                        $q->where('entidadeId', request('entidadeId'));
                    })->where('pendente', true);
                },
                'encaminhamentos.destinatarios.utilizador'
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

    public function totalPendentes()
    {
        try {

            $registos = Registo::with([
                'encaminhamentos' => function($query){
                    $query->where('pendente', true);
                },
                'encaminhamentos.destinatarios' => function ($query) {
                    $query->when('destinatarios', function($q) {
                        $q->where('entidadeId',  request('entidadeId'));
                    });
                },
                'encaminhamentos.destinatarios.utilizador'
            ])
            ->whereHas('encaminhamentos', function ($encaminhamento) {
                $encaminhamento->when('obrigatorioTodos' == true, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {
                        $destinatario->where('pendente', true)->where('entidadeId', request('entidadeId'));
                    })->where('pendente', true);
                }, function ($query) {
                    $query->whereHas('destinatarios', function ($destinatario) {
                        $destinatario->where('entidadeId', request('entidadeId'));
                    })->where('pendente', true);
                });
            });
           
            $totalPendentes = $registos->count();

            return response()->json($totalPendentes)->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo ocorreu mal, por favor contacte o administrador' . $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
