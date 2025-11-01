<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use DateInterval;
use App\Models\Tipo;
use App\Models\User;
use App\Models\Despesa;
use App\Models\Registo;
use App\Models\Entidade;
use App\Models\Categoria;
use App\Models\Transacao;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use App\Models\AnexoPendente;
use Illuminate\Http\Response;
use App\Models\Encaminhamento;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\DB;
use App\Models\RegistoDestinatario;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\EncaminhamentoExterno;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\EncaminhamentoDestinatario;

class DashboardController extends Controller
{
    public function index()
    {

        $registoTipo = $this->getTipoRegistoCount();
        $encaminhamentoInterno = $this->getEncaminhamentoCount(); 
        $encaminhamentoExterno = $this->getEncaminhamentoExternoCount();
        $encaminhamentoTotal = $encaminhamentoInterno + $encaminhamentoExterno;

        $spaceFiles = $this->getAvailableSpace();

        $espacoDisponivel = $spaceFiles['freeSpace']; 
        $espacoOcupado    = $spaceFiles['totalFilDirectory']; 
        $totalFicheiros   = $spaceFiles['filesCount']; 

        $data = [
            'tiposDocumentos'=>[
                'overview' => [
                    'documentosPorClassificar'=> $this->getDocumentosPorClassificar(),
                    'documentosTotalClassificados' => $this->getDocumentosTotalClassificados(),
                    'documentosHoje' => $this->getDocumentosHoje(),
                    'documentosPorLer' => $this->getDocumentosPorLer(),
                    'documentosResponder' => $this->getDocumentosPorResponder(),
                    'documentosForaPrazo' => $this->getDocumentosForaPrazo(),
                ],
                'labels' => Tipo::orderBy('id', 'desc')->get('nome')->pluck('nome'), 
                'series' => [
                        [
                            'name' => 'Prévisão',
                            'type' => 'line',
                            'data'=> $this->getTiposDocumentosCount('PREVISAO')
                        ],
                        [
                            'name'=> 'Documentos',
                            'type'=> 'column',
                            'data'=> $this->getTiposDocumentosCount('DOCUMENTOS')
                        ]
                ]
            ],
            'tipoRegisto' => [
                'labels' => ['ENTRADAS', 'SAIDAS', 'INTERNOS'], 
                'series' => $registoTipo,
                'overview' => [
                    'entrada' => $registoTipo[0],
                    'saida' => $registoTipo[1],
                    'interno' => $registoTipo[2]
                ]

            ],
            'utilizadores'=>[
                'cadastrados' => User::count(),
                'activos'     => User::count(),
            ],
            'tiposEntidades'=>[
                'cadastrados' => Entidade::where('activo', true)->count(),
                'activos'     => Entidade::where('activo', false)->count(),
            ],
            'tipos'=>[
                'cadastrados' => Tipo::where('activo', true)->count(),
                'activos'     => Tipo::where('activo', false)->count(),
            ],
            'encaminhamentos' => [
                'total' => $encaminhamentoTotal,
                'interno' => $encaminhamentoInterno,
                'externo'     => $encaminhamentoExterno
            ],
            'ultimosDocumentosRecebidos' => $this->ultimosDocumentosRecebidos(),
            'espacoDisco' => [
                'labels' => ['Espaço Disponível', 'Espaço Ocupado'], 
                'series' => [$espacoDisponivel, $espacoOcupado],
                'uniqueVisitors' => 212            
            ],
            'totalFicheiros' => $totalFicheiros,
            'tiposDocumentosEspaco'=>[
                'labels' => Tipo::orderBy('id', 'desc')->get('nome')->pluck('nome'), 
                'series' => [
                    [
                        'name' => 'Total de Ficheiros',
                        'type' => 'line',
                        'data'=> $this->getTiposDocumentosEspaco('PREVISAO')
                    ],
                    [
                        'name'=> 'Espaço Ocupado (GB)',
                        'type'=> 'column',
                        'data'=> $this->getTiposDocumentosEspaco('DOCUMENTOS')
                    ]
                ]
            ],
        ];

        return response()->json(['data'=>$data])->setStatusCode(Response::HTTP_OK);
    }



    public function getAvailableSpace()
    {
        // Detect OS and set the correct disk path
        $path = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'C:' : '/';

        $totalSpace = disk_total_space($path);
        $freeSpace  = disk_free_space($path);

        $totalFilDirectory = $this->recursiveDirectorySize(storage_path('app'));

        return [
            'freeSpace' => $freeSpace,
            'totalFilDirectory' => $totalFilDirectory[0],
            'filesCount' => $totalFilDirectory[1],
        ];
    }

    function recursiveDirectorySize($directory)
    {
        $size = 0;
        $count = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
                $count++;
            }
        }

        return [$size, $count];
    }


    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision);
    }

    private function formatBytesUnit($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }



    public function getEncaminhamentoCount(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        return Encaminhamento::whereBetween('created_at', [$data_inicio, $data_fim])->count();
    }

    public function getEncaminhamentoExternoCount(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        return EncaminhamentoExterno::whereBetween('created_at', [$data_inicio, $data_fim])->count();
    }

    public function ultimosDocumentosRecebidos()  {

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $registos = Registo::with([
            'tipo',
            'documento',
            'registoRemetente.entidade',
            'registoRemetente.utilizador',
            'registoDestinatario.entidade',
            'registoDestinatario.utilizador',
        ])
        ->whereBetween('data', [$data_inicio, $data_fim])
        ->limit(4)
        ->orderBy('id', 'desc')
        ->get();

      
        return $registos;
    }

    public function getTiposDocumentosCount($tipoTransacao){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $data = [];
        $fornecedores = Tipo::orderBy('id', 'desc')->get('id')->pluck('id');
        foreach($fornecedores as $value){
            
            if($tipoTransacao=='DOCUMENTOS'){
                $data[] = Registo::where('tipoId',  $value)->whereBetween('data', [$data_inicio, $data_fim])->get()->sum('id');
            }else{
                $data[] = Registo::where('tipoId',  $value)->whereBetween('data', [$data_inicio, $data_fim])
                ->get()
                ->map(function ($item, $key) {
                    $item->id += $item->id; 
                    return $item;
                })
                ->sum('id');
                
            }
        }

        return $data;
    }

    public function getTiposDocumentosEspaco($tipo)
    {
        $data = [];
        $fornecedores = Tipo::orderBy('id', 'desc')->get('nome')->pluck('nome');

        foreach ($fornecedores as $value) {
            $path = storage_path('app') . '/uploads/registos/documentos/' . $value;

            // Check if directory exists before calling recursiveDirectorySize
            if (is_dir($path)) {
                $dataSize = $this->recursiveDirectorySize($path);
                if ($tipo == 'DOCUMENTOS') {
                    $data[] = $this->bytesToGigabytes($dataSize[0]);
                } else {
                    $data[] = $dataSize[1];
                }
            } else {
                // Directory does not exist, return 0
                $data[] = 0;
            }
        }

        return $data;
    }

    public function bytesToGigabytes($bytes, $precision = 4) {
        return round($bytes / (1024 * 1024 * 1024), $precision);
    }

    public function getDocumentosPorClassificar(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $pendentesPorClassificar = AnexoPendente::where('classificado', false)->whereBetween('created_at', [$data_inicio, $data_fim])->get()->count();

        return $pendentesPorClassificar;
    }

    public function getDocumentosTotalClassificados(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();
       
        $total = Registo::whereBetween('created_at', [$data_inicio, $data_fim])->count();

        return $total;
    }

    public function getDocumentosHoje(){
        return Registo::whereDate('created_at', now()->toDateString())->count();
    }

    public function getDocumentosPorLer(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $totalDestinatario   = RegistoDestinatario::whereBetween('created_at', [$data_inicio, $data_fim])->where('visualizado', false)->count();
        // $totalEncaminhamento = EncaminhamentoDestinatario::whereBetween('created_at', [$data_inicio, $data_fim])->where('visualizado', false)->count();

        return $totalDestinatario;
    }

    public function getDocumentosPorResponder(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $total = EncaminhamentoDestinatario::whereBetween('created_at', [$data_inicio, $data_fim])->where('pendente', true)->count();

        return $total;
    }

    public function getDocumentosForaPrazo(){

        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $total = Encaminhamento::whereBetween('created_at', [$data_inicio, $data_fim])->where('pendente', true)->whereDate('dataLimite', '<', $data_fim)->count();

        return $total;
    }

    public function getTipoRegistoCount(){
        $data_inicio = $this->getDtInitial();
        $data_fim    = $this->getDtEnd();

        $totalEntada = Registo::whereBetween('created_at', [$data_inicio, $data_fim])->where('registoTipo', 'ENTRADA')->count();
        $totalSaida = Registo::whereBetween('created_at', [$data_inicio, $data_fim])->where('registoTipo', 'SAIDA')->count();
        $totalInterno = Registo::whereBetween('created_at', [$data_inicio, $data_fim])->where('registoTipo', 'INTERNO')->count();

        return [$totalEntada, $totalSaida, $totalInterno];
    }

  
    public function getDtInitial(){

        if(isset($_GET['dtInitial']) && $_GET['dtInitial'] != ''){
            $data_inicio = $_GET['dtInitial'];
        }else{

            $currentDate = new DateTime(); // Get current date
            $currentDate->sub(new DateInterval('P6M')); // Subtract 6 months
            $data_inicio = $currentDate->format('Y-m-d');
        }

        return $data_inicio;
    }

    public function getDtEnd(){

        $date = date("Y-m-j", strtotime(date("Y") . "-" . date("m") . "-" . (date("j") )));
        $data_fim    = (isset($_GET['dtEnd']) && $_GET['dtEnd'] != '') ? $_GET['dtEnd'] . ' 23:59:59' : now()->toDateString() . ' 23:59:59';

        return $data_fim;
    }

}
