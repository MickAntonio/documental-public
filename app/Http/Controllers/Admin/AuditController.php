<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OwenIt\Auditing\Models\Audit;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    public function index()
    {
        try {

            $auditoria = Audit::with(['user'])->where('id','>',0)->orderBy('id', 'desc');

            if(request('userId') && !isNullOrEmpty(request('userId'))){
                $auditoria->where('user_id',request('userId'));
            }
            
            if(request('event') && !isNullOrEmpty(request('event'))){
                $auditoria->where('event',request('event'));
            }

            if(request('registoId') && !isNullOrEmpty(request('registoId'))){
                $auditoria->where('tags', 'LIKE', '%registoId=' . request('registoId').'%');
            }

            if(request('dtInitial') && !isNullOrEmpty(request('dtInitial')) && (request('dtEnd') && !isNullOrEmpty('dtEnd'))){
                $from   = date_format(date_create(request('dtInitial').'00:00:00'),'Y-m-d H:i:s');
                $to     = date_format(date_create(request('dtEnd').'23:59:59'),'Y:m:d H:i:s');
                $auditoria->whereBetween("created_at", [$from,$to]);
            } 

            $auditoria = $auditoria->paginate(request('size'),['*'],'page',request('page')+1);

            return response()->json(repage($auditoria))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {

            $auditoria  = Audit::with(['user'])->find($id);

            if($auditoria == null){
                 return response()->json(['message'=>' A auditoria não foi encontrada'])->setStatusCode(Response::HTTP_NOT_FOUND);
            }else{
                 return response()->json(['data'=>$auditoria])->setStatusCode(Response::HTTP_CREATED);
            }
        } catch (\Throwable $th) {

            return response()->json(['message'=>'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function destroy($id)
    {
      try {
            $auditoria  = Audit::find($id);
            if($auditoria == null){
                 return response()->json(['message'=>' A auditoria não foi encontrada'])->setStatusCode(Response::HTTP_NOT_FOUND);
            }else{
                $auditoria->delete();
                return response()->json(['message'=>' A auditoria excluida com sucesso '])->setStatusCode(Response::HTTP_NO_CONTENT);
            }
        } catch (\Throwable $th) {
                   return response()->json(['message'=>'Algo ocorreu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function destroyByPeriod(Request $request)
    {
      try {
            $validator = Validator::make($request->all(),
            [
                    "data_start" => ['required','date'],
                    "data_end" => ['required','date'],
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            $data_start = date('Y-m-d', strtotime(request('data_start')));
            $data_end = date('Y-m-d', strtotime(request('data_end')));

            Audit::whereBetween("created_at", [$data_start, $data_end])->delete();
            return response()->json(['message'=>' Dados excluidos com sucesso '])->setStatusCode(Response::HTTP_NO_CONTENT);

        } catch (\Throwable $th) {
                   return response()->json(['message'=>$th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

}
