<?php

namespace App\Http\Controllers\Anexos;

use App\Models\Anexo;
use RecursiveTreeIterator;

use \ConvertApi\ConvertApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\HistoricoAnexo;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class HistoricoAnexoController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($anexo, $utilizadorId)
    {
        try {

            $localizacao = preg_replace('/\/[^\/]*$/', '', $anexo->localizacao) . '/historico/';
            
            $diretory = storage_path('app/' . $localizacao);

            if (!file_exists($diretory)) {
                mkdir($diretory, 0777, true);
            }

            $fileName = $anexo->nome . '_' . uniqid() . $anexo->extensao;

            copy(storage_path('app/' . $anexo->localizacao), $diretory . $fileName);

            $anexoHistorico = new HistoricoAnexo();
            $anexoHistorico->anexoId      = $anexo->id;
            $anexoHistorico->nome         = $anexo->nome;
            $anexoHistorico->tamanho      = $anexo->tamanho;
            $anexoHistorico->versao       = $anexo->versao;
            $anexoHistorico->extensao     = $anexo->extensao;
            $anexoHistorico->observacao   = $anexo->observacao;
            $anexoHistorico->criadoPor    = $anexo->criadoPor;
            $anexoHistorico->editadoPor   = $anexo->editadoPor;
            $anexoHistorico->localizacao  = $localizacao . $fileName;
            $anexoHistorico->registoId    = $anexo->registoId;
            $anexoHistorico->utilizadorId = $utilizadorId;
            $anexoHistorico->save();

            return true;

        } catch (\Throwable $th) {
            return false;
        }

    }

    public function view($anexoId)
    {
        $anexo = HistoricoAnexo::find($anexoId);

        if ($anexo != null) {
            return Storage::download($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function convertToPdf(Request $request)
    {
        try {
            // saved anexos
            //return response()->json(["data"=>$request->anexos], Response::HTTP_CREATED);

            $anexos = [];
            $i = 0;
            foreach ($request->anexos as $key => $value) {

                $anexo = Anexo::where('id', $value)->with(['registo.documento', 'registo.tipo'])->first();
    
                if($anexo!=null && $this->convertibleExtesion($anexo->extensao)){
    
                    ConvertApi::setApiSecret('oDhuDqMbpkgSYS3K');
    
                    $result = ConvertApi::convert('pdf', ['File' => storage_path('app/' . $anexo->localizacao)]);
            
                    # save to file
    
                    // Path to save the file
                    $path = 'uploads/registos/documentos/' . $anexo->registo->tipo->nome . '/' . date('Y') . '/' . explode('/', $anexo->registo->documento->numeroDocumento)[1];
    
                    // Make documento diretory
                    if (!file_exists(storage_path('app/' . $path))) {
                        mkdir(storage_path('app/' . $path), 0777, true);
                    }
                    
                    // File name
                    $fileName = $anexo->nome . '_pdf';
    
                    // Create a new file with name and extension
                    $filePath = $path . '/' . $fileName . '.pdf';
    
                    if (file_exists(storage_path('app/' . $filePath))) {
                        $filePath = $path . '/' . $fileName . '_' . uniqid() . '.pdf';
                    }
    
                    // Write the decoded data to the new file
                    $result->getFile()->save(storage_path('app/' . $filePath));
            
                    
                    $anexoNew = new Anexo();
                    $anexoNew->nome        = $anexo->nome;
                    $anexoNew->tamanho     = filesize(storage_path('app/' . $filePath));
                    $anexoNew->extensao    = '.pdf';
                    $anexoNew->estado      = 'IN';
                    $anexoNew->localizacao = $filePath;
                    $anexoNew->registoId   = $anexo->registo->id;
                    $anexoNew->versao      = $anexo->versao;
                    $anexoNew->criadoPor   = Auth::user()->name;
                    $anexoNew->editadoPor  = Auth::user()->name;
                    $anexoNew->observacao  = 'Convertido por ' . Auth::user()->name;
                    $anexoNew->save();
                    
                    $anexos[$i] = $anexoNew;
                    $i++;
                }
    
            }

            return response()->json(['data' => $anexos], Response::HTTP_CREATED);

        } catch (\Throwable $th) {
            return response()->json(['message' => "Ocorreu algum problema ao converter o(s) ficheiro(s) em PDF"], Response::HTTP_BAD_REQUEST);
        }

    }

    public function convertibleExtesion($extension){
        $extensions = [
            '.docx',
            '.doc',
            '.png',
            '.jpg',
            '.jpeg',
            '.pptx',
            '.ppt',
            '.xlsx',
            '.xls',
            '.txt'
        ];
        
        if(in_array($extension, $extensions)){
            return true;
        }

        return false;
    }

    public function folders(Request $request){
        //$directory = $this->directoryTree(storage_path('app'));
        //dd($directory);
        //$this->recurseveTree();
        //return dd($this->listFolderFiles(storage_path('app')), Response::HTTP_OK);;

       
        $data = $this->listFolderFiles(storage_path('app/uploads/registos/documentos'));

        if(isset($request->folderId) && $request->folderId!=null){
            
        }

        return response()->json($data, Response::HTTP_OK);;
    }

    function listFolderFiles3($dir){
        $ffs = scandir($dir);
        
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        unset($ffs[array_search('.gitignore', $ffs, true)]);
        
        // prevent empty ordered elements
        if (count($ffs) < 1)
            return null;
        
        $result = array();
        foreach($ffs as $ff){
            $entry = array();
            $entry['name'] = $ff;
            if(is_dir($dir.'/'.$ff)){
                $entry['type'] = 'dir';
                $entry['children'] = $this->listFolderFiles($dir.'/'.$ff);
            }
            else{
                $entry['type'] = 'file';
            }
            $result[] = $entry;
        }
        return $result;
    }

    
    function listFolderFiles($dir, $parentId = null) {
        $ffs = scandir($dir);
    
        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);
        unset($ffs[array_search('.gitignore', $ffs, true)]);
    
        // prevent empty ordered elements
        if (count($ffs) < 1) {
            return null;
        }
    
        $result = array();
        foreach($ffs as $ff) {
    
            $entry = array();
            $entry['id'] = md5($dir.$ff); //Crypt::encryptString($dir.$ff);//uniqid(); // Generate a new unique ID for the entry
            $entry['name'] = $ff;
            $paths = explode('/uploads/registos/documentos', $dir . '/'. $ff);

            if(count($paths)==1){
                $entry['path'] = $paths[0];
            }else{
                $entry['path'] = $paths[1];
            }
            //$entry['location'] = $dir;
            if(is_dir($dir.'/'.$ff)){
                $entry['type'] = 'folder';
                $entry['entryType'] = 'folder';
                $entry['folderId'] = $parentId; // Set the folder ID for subdirectories
                
                // Get the list of subentries for this directory
                $subentries = $this->listFolderFiles($dir.'/'.$ff, $entry['id']); // Pass the ID of the current directory as the parent ID
                
                // Count the number of files in the subentries array
                $numFiles = 0;

                if (!empty($subentries)) {
                    foreach ($subentries as $subentry) {
                        if ($subentry['entryType'] === 'file') {
                            $numFiles++;
                        }
                    }
                }

                
                // Add the number of files to the entry
                $entry['contents'] = $numFiles;
                
                $result[] = $entry;
                
                if (!empty($subentries)) {
                    foreach ($subentries as $subentry) {
                        $result[] = $subentry;
                    }
                }
            } else {
                $entry['entryType'] = 'file';
                $entry['type'] = strtoupper(pathinfo($ff, PATHINFO_EXTENSION));
                $entry['folderId'] = $parentId; // Set the folder ID for files
                $entry['extension'] =strtoupper(pathinfo($ff, PATHINFO_EXTENSION)); // Add the file extension to the entry
                $entry['size'] = filesize($dir . '/' . $ff);
                $entry['createdAt'] = date("Y-m-d H:i:s", filectime($dir . '/' . $ff)); // Add the file creation date to the entry
                $entry['modifiedAt'] = date("Y-m-d H:i:s", filemtime($dir . '/' . $ff)); // Add the file update date to the entry
                //$entry['location'] = $dir;
                $result[] = $entry;
            }
        }
        return $result;
    }
    
    
    
    function directoryTree($path)
    {
        // Create a RecursiveDirectoryIterator object for the specified path
        $iterator = new RecursiveDirectoryIterator($path);
    
        // Create a RecursiveIteratorIterator object for the directory iterator
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
    
        // Initialize an empty array to store the tree
        $tree = array();
    
        // Loop through the iterator
        foreach ($iterator as $fileinfo) {
            // Skip hidden files and directories
            if (in_array($fileinfo->getBasename(), array('.', '..', '.gitignore'))) {
                continue;
            }
    
            // Get the file path relative to the base path
            $filePath = $fileinfo->getPathname();
            $filePath = str_replace($path . DIRECTORY_SEPARATOR, '', $filePath);
    
            // Split the file path into an array of directories
            $dirs = explode(DIRECTORY_SEPARATOR, $filePath);
    
            // Initialize the current node as the root of the tree
            $currentNode = &$tree;
    
            // Loop through each directory in the path and add it to the tree
            foreach ($dirs as $dir) {
                // If the directory doesn't exist in the tree yet, create it
                if (!isset($currentNode[$dir])) {
                    $currentNode[$dir] = array();
                }
    
                // Set the current node to the newly created directory
                $currentNode = &$currentNode[$dir];
            }
    
            // Add the file to the current node
            $currentNode[] = $fileinfo->getFilename();
        }
    
        return $tree;
    }

    public function getAnexo(Request $request)
    {
        $anexo = Anexo::where('localizacao', 'LIKE', '%' . $request->path)->first();

        if ($anexo != null) {
            return $anexo;
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getAnexoDownload(Request $request)
    {
        $anexo = Anexo::where('localizacao', 'LIKE', '%' . $request->path)->first();

        if ($anexo != null) {
            return Storage::download($anexo->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

   
}
