<?php

namespace App\Http\Controllers\Anexos;

use ZipArchive;
use App\Models\Tipo;
use App\Models\Anexo;
use App\Models\Anexos;
use App\Models\Registo;
use App\Models\Template;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Validator;

class AnexosController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'nome'  => 'required|string|max:250',
                'tamanho'  => 'required',
                'registoId'  => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $anexo = new Anexo();
            $anexo->nome        = $request->nome;
            $anexo->tamanho     = $request->tamanho;
            $anexo->versao      = $request->versao;
            $anexo->estado      = $request->estado;
            $anexo->observacao  = $request->observacao;
            $anexo->criadoPor   = $request->criadoPor;
            $anexo->editadoPor  = $request->editadoPor;
            $anexo->localizacao = $request->localizacao;
            $anexo->registoId   = $request->registoId;
            $anexo->save();

            return response()->json($anexo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDragDrop(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'tipo'  => 'required',
                'numeroDocumento'  => 'required',
                'anexos'  => 'required',
                'registoId'  => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            // saved anexos
            $anexos = [];

            // Path to save the file
            $path = 'uploads/registos/documentos/' . $request->tipo . '/' . date('Y') . '/' . explode('/', $request->numeroDocumento)[1];

            // Make documento diretory
            if (!file_exists(storage_path('app/' . $path))) {
                mkdir(storage_path('app/' . $path), 0777, true);
            }
            // Save all anexos
            foreach ($request->anexos as $key => $anexoRequest) {

                // Decode the base64 data
                $base64 = explode(',', $anexoRequest['file'])[1];
                $decodedData = base64_decode($base64);

                // File name
                $fileName = $anexoRequest['name'];

                // Create a new file with name and extension
                $filePath = $path . '/' . $fileName . $anexoRequest['extension'];

                if (file_exists(storage_path('app/' . $filePath))) {
                    $filePath = $path . '/' . $fileName . ' ' . uniqid() . $anexoRequest['extension'];
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
                $anexo->registoId   = $request->registoId;
                $anexo->save();

                // Add metadates
                if ($anexoRequest['extension'] == '.docx' || $anexoRequest['extension'] == '.doc') {

                    $file = storage_path($filePath);

                    $this->addRelationships($file);

                    $this->addContentTypes($file);
        
                    $this->addCustomPropertiesDocx($file, ['anexoId' => $anexo->id, 'registoId' => $request->registoId, 'versao' => $anexo->versao]);
                }

                // saved anexos list
                $anexos[$key] = $anexo;
            }

            DB::commit();

            return response()->json(['data' => $anexos])->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollback();

            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function templateFile()
    {

        try {

            $file = storage_path('app/uploads/registos/documentos/Contracto de Trabalho/2023/00002/teste4.docx');

            $rels =  $this->addRelationships($file);

            $contentTypes =  $this->addContentTypes($file);

            $result = $this->addCustomPropertiesDocx($file, ['anexoId' => 3, 'registoId' => 11]);

            return response()->json(['data' => [$file, $rels, $contentTypes, $result]])->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollback();

            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function download($anexoId, $utilizadorId)
    {
        $anexo = Anexo::find($anexoId);

        if ($anexo != null) {

             // Add metadates
             if ($anexo->extensao == '.docx' || $anexo->extensao == '.doc') {

                $file = storage_path('app/' . $anexo->localizacao);

                $this->addRelationships($file);

                $this->addContentTypes($file);
    
                $this->addCustomPropertiesDocx($file, ['anexoId' => $anexo->id, 'registoId' => $anexo->registoId, 'versao' => $anexo->versao, 'utilizadorId'=> $utilizadorId]);
            }

            return Storage::download($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function downloadMultiples(Request $request)
    {
        foreach ($request->anexos as $key => $value) {

            $anexo = Anexo::find($value);

            if ($anexo != null) {
                // Add metadates
                if ($anexo->extensao == '.docx' || $anexo->extensao == '.doc') {

                    $file = storage_path('app/' . $anexo->localizacao);

                    $this->addRelationships($file);

                    $this->addContentTypes($file);
        
                    $this->addCustomPropertiesDocx($file, ['anexoId' => $anexo->id, 'registoId' => $anexo->registoId, 'versao' => $anexo->versao, 'utilizadorId'=> Auth::user()->id]);
                }

                $files[] = $anexo->localizacao;
            }

        }
        
        $zipName = Auth::user()->id.'.zip'; // The name of the downloaded zip archive

        // Create a new ZipArchive instance and open the zip file
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/downloads/' . $zipName), ZipArchive::CREATE) === true) {

            // Loop through the files and add them to the zip archive
            foreach ($files as $file) {
                $filename = basename($file);
                $zip->addFile(storage_path('app/' . $file), $filename);
            }

            // Close the zip file
            $zip->close();

            // Download the zip archive
            return Storage::download('downloads/'.$zipName);
        }

    }

    public function view($anexoId)
    {
        $anexo = Anexo::find($anexoId);

        if ($anexo != null) {
            return Storage::download($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkOut($anexoId)
    {
        $anexo = Anexo::find($anexoId);
        if ($anexo != null) {
            if($anexo->estado=='IN'){
                $anexo->estado = 'OUT';
                $anexo->checkoutUtilizadorId = Auth::user()->id;
                $anexo->save();

                 // Add metadates
                if ($anexo->extensao == '.docx' || $anexo->extensao == '.doc') {

                    $file = storage_path('app/' . $anexo->localizacao);

                    $this->addRelationships($file);

                    $this->addContentTypes($file);
        
                    $this->addCustomPropertiesDocx($file, ['anexoId' => $anexo->id, 'registoId' => $anexo->registoId, 'versao' => $anexo->versao, 'utilizadorId'=> Auth::user()->id]);
                }

                return Storage::download($anexo->localizacao);
            }else{
                return response()->json(['message' => "Ficheiro sob edição exclusiva"]);
            }
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function checkIn($anexoId)
    {
        $anexo = Anexo::find($anexoId);
        if ($anexo != null) {
            if($anexo->estado=='OUT' && $anexo->checkoutUtilizadorId==Auth::user()->id){
                $anexo->estado = 'IN';
                $anexo->checkoutUtilizadorId = null;
                $anexo->save();
                return response()->json(['message' => "Edição exclusiva removida"]);
            }else{
                return response()->json(['message' => "O ficheiro já se encontra no estado 'check in'"]);
            }
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function url($anexoId)
    {
        $anexo = Anexo::find($anexoId);

        if ($anexo != null) {
            return Storage::url($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }


        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAnexoTemplate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'registoId'  => 'required|integer',
                'tipoId'  => 'required|integer',
                'templateId'  => 'required|integer',
                'documentoId'  => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

                $template = Template::find($request->templateId);
                // $registo = Registo::find($request->documentoId);
                $documento = Documento::find($request->documentoId);

                if($template!=null && $documento!=null){

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
                    $anexo->criadoPor   = Auth::user()->username;
                    $anexo->editadoPor  = Auth::user()->username;
                    $anexo->localizacao = $filePath;
                    $anexo->registoId   = $request->registoId;;
                    $anexo->save();

                }
                
            return response()->json([])->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update anexo metadados
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateMetedados(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id'  => 'required',
                'nome'  => 'required|string|max:250',
                'versao'  => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $anexo = Anexo::find($request->id);

            if ($anexo == null) {
                return response()->json(['message' => 'Anexo não encotrada'], Response::HTTP_NOT_FOUND);
            }

            $anexo->nome        = $request->nome;
            $anexo->versao      = $request->versao;
            $anexo->observacao  = $request->observacao;
            $anexo->editadoPor  = Auth::user()->name;
            $anexo->save();

            return response()->json($anexo)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador' . $th], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $validator = Validator::make(['id' => $id], [
                'id'  => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }
            $anexo = Anexo::findOrFail($id);

            if ($anexo == null) {
                return response()->json(['message' => 'Anexo não encontrada'], Response::HTTP_NOT_FOUND);
            }

            $anexoHistorico = new HistoricoAnexoController();
            $anexoHistorico->store($anexo, Auth::user()->id);

            $status = unlink(storage_path('app/' . $anexo->localizacao));    

            if($status){  
                $anexo->delete();
                return response()->json(['message' => 'Anexo excluida'], Response::HTTP_NO_CONTENT);
            }else{  
                return response()->json(['message' => 'Não foi possivel excluir o ficheiro'], Response::HTTP_OK);
            }  
            
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador: '.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

        /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMultiples(Request $request)
    {
        try {

            /*$validator = Validator::make(['id' => $request], [
                'anexos'  => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }*/

            foreach ($request->anexos as $key => $value) {
                $anexo = Anexo::findOrFail($value);

                if ($anexo != null) {
                    $anexoHistorico = new HistoricoAnexoController();
                    $anexoHistorico->store($anexo, Auth::user()->id);
        
                    $status = unlink(storage_path('app/' . $anexo->localizacao));    
        
                    if($status){  
                        $anexo->delete();
                    } 
                }
            }
           
            return response()->json(['message' => 'Anexo(s) excluido(s)'], Response::HTTP_NO_CONTENT);
            
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
    public function addInDocumentChange(Request $request)
    {
        // Get the binary stream from the request body
        $stream = $request->getContent();

        $anexo = Anexo::find($request->header('anexoId'));

        if ($anexo == null) {
            return response('Anexo não encotrada');
        }

        if($anexo->estado=='OUT' && $anexo->checkoutUtilizadorId!=$request->header('utilizadorId')){
            return response('O documento esta sob edição exclusiva de um outro utilizador!');
        }

        if($request->header('versao') > $anexo->versao){

            // History
            $anexoHistorico = new HistoricoAnexoController();
            $res = $anexoHistorico->store($anexo, $request->header('utilizadorId'));
    
            // Save the stream to a file in the storage/app/uploads directory
            file_put_contents(storage_path('app/' . $anexo->localizacao), $stream);
    
            $anexo->versao = $request->header('versao');
            $anexo->estado = 'IN';
            $anexo->checkoutUtilizadorId = null;
            $anexo->save();

            // Return a response to the client
            return response('Documento '.$anexo->nome.' Salvo com Successo ! res: '.$res);
        }else{
            // Return a response to the client
            return response('Não foi possivel salvar as alterações, porque a versão actual está mais actualizada!');
        }
        
    }


    /**
     * Add custom properties to word document.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function addCustomPropertiesDocx($file, $properties)
    {
        // Open the DOCX file as a ZIP archive
        $zip = new \ZipArchive();

        if ($zip->open($file) === true) {

            // Get the contents of the core document part
            $core = $zip->getFromName('docProps/custom.xml');

            // Create the custom property elements
            $customProps  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

            $i = 2;

            $customProps .= '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/custom-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">';

            foreach ($properties as $name => $value) {
                $customProps .= "<property fmtid=\"{D5CDD505-2E9C-101B-9397-08002B2CF9AE}\" pid=\"$i\" name=\"$name\"> <vt:lpwstr>" . $value . "</vt:lpwstr></property>";
                $i++;
            }

            $customProps .= '</Properties>';

            // Update the core document part in the ZIP archive
            $zip->addFromString('docProps/custom.xml', $customProps);
            
            // Close the ZIP archive
            $zip->close();
            return  true;
        } else {
            return  false;
        }
    }

     /**
     * Add custom properties to word document.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function addRelationships($file)
    {
        // Open the DOCX file as a ZIP archive
        $zip = new \ZipArchive();

        if ($zip->open($file) === true) {

            // Get the contents of the core document part
            $rels = $zip->getFromName('_rels/.rels');

            // Create the custom property elements
            $relationships  = 
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml" />'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml" />'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml" /> '
            .'<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/custom-properties" Target="docProps/custom.xml" />'
            .'</Relationships>';

            // Update the core document part in the ZIP archive
            $zip->addFromString('_rels/.rels', $relationships);
            
            // Close the ZIP archive
            $zip->close();
            return  true;
        } else {
            return  false;
        }
    }

         /**
     * Add custom properties to word document.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function addContentTypes($file)
    {
        // Open the DOCX file as a ZIP archive
        $zip = new \ZipArchive();

        if ($zip->open($file) === true) {

            // Get the contents of the core document part
            $types = $zip->getFromName('[Content_Types].xml');

            // Create the custom property elements
            $contentTypes  = 
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" />'
                .'<Default Extension="xml" ContentType="application/xml" />'
                .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml" />'
                .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml" />'
                .'<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml" />'
                .'<Override PartName="/word/webSettings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.webSettings+xml" />'
                .'<Override PartName="/word/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml" />'
                .'<Override PartName="/word/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml" />'
                .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml" />'
                .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml" />'
                .'<Default Extension="png" ContentType="image/png" />'
                .'<Default Extension="jpeg" ContentType="image/jpeg" />'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml" />'
                .'<Default Extension="xml" ContentType="application/xml" />'
                .'<Override PartName="/customXml/itemProps1.xml" ContentType="application/vnd.openxmlformats-officedocument.customXmlProperties+xml" />'
                .'<Override PartName="/customXml/itemProps2.xml" ContentType="application/vnd.openxmlformats-officedocument.customXmlProperties+xml" />'
                .'<Override PartName="/customXml/itemProps3.xml" ContentType="application/vnd.openxmlformats-officedocument.customXmlProperties+xml" />'
                .'<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml" />'
                .'<Override PartName="/word/footnotes.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footnotes+xml" />'
                .'<Override PartName="/word/endnotes.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml" />'
                .'<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml" />'
                .'<Override PartName="/word/glossary/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.glossary+xml" />'
                .'<Override PartName="/word/glossary/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml" />'
                .'<Override PartName="/word/glossary/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml" />'
                .'<Override PartName="/word/glossary/webSettings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.webSettings+xml" />'
                .'<Override PartName="/word/glossary/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml" />'
                .'<Override PartName="/word/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml" />'
                .'<Override PartName="/docProps/custom.xml" ContentType="application/vnd.openxmlformats-officedocument.custom-properties+xml" />'
            .'</Types>';

            // Update the core document part in the ZIP archive
            $zip->addFromString('[Content_Types].xml', $contentTypes);
            
            // Close the ZIP archive
            $zip->close();
            return  true;
        } else {
            return  false;
        }
    }
}
