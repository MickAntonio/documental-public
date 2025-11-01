<?php

namespace App\Http\Controllers\Anexos;

use Illuminate\Http\Request;
use App\Models\AnexoPendente;
use Illuminate\Http\Response;
use App\Models\LocalizacaoScanner;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class AnexoPendenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $anexosPendentes = AnexoPendente::with(['utilizador', 'origem'])->where('id', '>', 0);

            if(request('pesquisaPor') && !isNullOrEmpty(request('pesquisaPor'))){
                $anexosPendentes->where('nome', 'like', '%' . request('pesquisaPor') . '%');
            }

            if(request('utilizadorId') && !isNullOrEmpty(request('utilizadorId'))){
                $anexosPendentes->where('utilizadorId', request('utilizadorId'));
            }

            if(request('localizacaoScannerId') && !isNullOrEmpty(request('localizacaoScannerId'))){
                $anexosPendentes->where('localizacaoScannerId', request('localizacaoScannerId'));
            }

            if(request('classificados') && !isNullOrEmpty(request('classificados'))){
                $anexosPendentes->where('classificado', request('classificados'));
            }else{
                $anexosPendentes->where('classificado', false);
            }

            if(request('periodoInicio') && !isNullOrEmpty(request('periodoInicio')) && (request('periodoFim') && !isNullOrEmpty('periodoFim'))){
                $from   = date_format(date_create(request('periodoInicio').'00:00:00'),'Y-m-d H:i:s');
                $to     = date_format(date_create(request('periodoFim').'23:59:59'),'Y:m:d H:i:s');
                $anexosPendentes->whereBetween("created_at", [$from, $to]);
            } 

            $anexosPendentes->orderBy('id', 'desc');

            $anexosPendentes = $anexosPendentes->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($anexosPendentes))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try{

            
            // Handle File Save
            $file = $_FILES['file'];

            $validator = Validator::make($request->all(), [
                'file'  => 'required'
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            // File Localização Scanner
            $scannerLocalizacao = LocalizacaoScanner::where('codigo', $request->codigoScanner)->first();
            $scannerCodigo = null;

            if($scannerLocalizacao!=null){
                $scannerCodigo = $scannerLocalizacao->codigo;
            }

            // Path to save the file
            $uploadDir = 'uploads/registos/documentos/pendentes/' . date('Y');

            // Make documento diretory
            if (!file_exists(storage_path('app/' . $uploadDir))) {
                mkdir(storage_path('app/' . $uploadDir), 0777, true);
            }
        
            // Generate a unique filename to avoid overwriting existing files
            $fileName = uniqid() . '_' . $file['name'];
            $uploadDirFile = $uploadDir .'/' . $fileName;
            $filePath = storage_path('app/' . $uploadDirFile);
        
            // Move the uploaded file to the specified folder
            if (move_uploaded_file($file['tmp_name'], $filePath)) {

                // Get file extension
                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                // Get file size
                $fileSize = filesize($filePath);

                // File successfully saved
                $anexoPendente = new AnexoPendente();
                $anexoPendente->nome      = $this->removeFileExtension($file['name']);
                $anexoPendente->extensao  = '.'.$fileExtension;
                $anexoPendente->tamanho   = $fileSize;
                $anexoPendente->localizacaoScannerId   = $scannerCodigo;
                $anexoPendente->criadoPor = $request->user;
                $anexoPendente->localizacao   = $uploadDirFile;
                $anexoPendente->utilizadorId  = $request->utilizadorId;
                $anexoPendente->save();

                return response()->json(array('success' => true, 'file_name' => $fileName))->setStatusCode(Response::HTTP_CREATED);
            } else {
                // Error occurred while saving the file
                return response()->json(array('success' => false, 'error' => 'Failed to save the file.'));
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function removeFileExtension($inputString) {
        // Find the position of the last dot in the string
        $lastDotPosition = strrpos($inputString, '.');
    
        if ($lastDotPosition !== false) {
            // Remove everything after the last dot
            $outputString = substr($inputString, 0, $lastDotPosition);
            return $outputString;
        } else {
            // No dot found, return the original string
            return $inputString;
        }
    }

    public function view($anexoId)
    {
        $anexo = AnexoPendente::find($anexoId);

        if ($anexo != null) {
            return Storage::download($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnexoPendente $anexoPendente)
    {
        //
    }
}
