<?php

namespace App\Http\Controllers;

use Elibyy\TCPDF\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\EtiquetaProtocolo;
use Illuminate\Support\Facades\Auth;
use Elibyy\TCPDF\Facades\TCPDF as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EtiquetaProtocoloController extends Controller
{
    public function index()
    {
        try {

            $etiquetas = EtiquetaProtocolo::where('id', '>', 0);

            if(request('nome') && !isNullOrEmpty(request('nome'))){
                $etiquetas->where('nome', 'like', '%'.request('nome').'%');
            }

            if(request('order') && !isNullOrEmpty(request('order'))){
                if(request('sort') && !isNullOrEmpty(request('sort'))){
                    $etiquetas->orderBy(request('sort'), request('order'));
                }
            }

            if(isNullOrEmpty(request('size')) && isNullOrEmpty(request('page'))) {
                $etiquetas = $etiquetas->paginate($etiquetas->count(), ['*'], 'page', 1);

                return response()->json(repage($etiquetas))->setStatusCode(Response::HTTP_OK);
            }

            $etiquetas = $etiquetas->paginate(request('size'), ['*'], 'page', request('page')+1);

            return response()->json(repage($etiquetas))->setStatusCode(Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function validarProtocolo($codigo)
    {
        try {
            $etiqueta = EtiquetaProtocolo::whereRaw("FIND_IN_SET('".$codigo."', codigos) > 0")->get();

            if ($etiqueta->count()==0) {
                return response()->json(['message' => 'Protocolo não encontrada'], Response::HTTP_NO_CONTENT);
            }

            $result = $etiqueta->first();
            $result->zpl = $this->etiquetaZPLStr($result->nome, $result->result, $codigo);

            return response()->json($result)->setStatusCode(Response::HTTP_CREATED);

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
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'nome'  => 'required|string|max:50',
                'quantidade'  => 'required',
                'duplicacao'  => 'required',
            ]);

            if ($validator->fails()) {
               return response()->json([$validator->errors()->all()], Response::HTTP_BAD_REQUEST);
            }

            $etiqueta = new EtiquetaProtocolo();
            $etiqueta->nome = $request->nome;
            $etiqueta->quantidade = $request->quantidade;
            $etiqueta->duplicacao = $request->duplicacao;
            $etiqueta->incluirData  = $request->incluirData;
            $etiqueta->utilizadorId = Auth::user()->id;
            $etiqueta->localizacao = 'NA';
            $etiqueta->save();

            $this->gerarEtiquetasPdfZPL($etiqueta);

            if($request->quantidade==1 && $request->duplicacao<=1){
                $etiqueta->zpl = $this->etiquetaZPLStr($etiqueta->nome, $etiqueta->data, $etiqueta->codigos);
            }

            return response()->json($etiqueta)->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Algo correu mal, por favor contacte o administrador'.$th], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function gerarEtiquetasPdf($etiqueta) 
    {
        
        PDF::SetTitle('Etiquetas Protocolos');
        PDF::AddPage();
        
        PDF::SetFont('helvetica', '', 9);

        $style = array(
            'position' => 'C',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => '0',
            'vpadding' => '0',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        // QUANTIDADE
        $codigosGerados = null;
        for ($i=1; $i <= $etiqueta->quantidade; $i++) { 

            $code = sequenceNextByCodeSepator('ETIQUETA', '');

            // DUPLICAÇÃO
            for ($j=1; $j <= $etiqueta->duplicacao; $j++) { 
                PDF::Cell(0, 0, $etiqueta->nome, 0, 1, 'C');
                PDF::write1DBarcode($code, 'C39', '', '', '', 18, 0.4, $style, 'N');

                if($etiqueta->incluirData){
                    PDF::Cell(0, 0, date('Y-m-d'), 0, 1, 'C');
                }

                PDF::Ln();
            }

            if($i==1){
                $codigosGerados = $code;
            }else{
                $codigosGerados = $codigosGerados . ',' . $code;
            }
        }

        // Make documento diretory
        if (!file_exists(storage_path('app/protocolos'))) {
            mkdir(storage_path('app/protocolos'), 0777, true);
        }

        $path = 'protocolos/protocolo' . $etiqueta->id . '.pdf';

        PDF::Output(storage_path('app/' . $path), 'F');

        $etiqueta->localizacao = $path;
        $etiqueta->codigos = $codigosGerados;
        $etiqueta->save();
    }

    public function gerarEtiquetasPdfZPL($etiqueta) 
    {
      
        $etiquetasZPL = "";
        // QUANTIDADE
        $codigosGerados = null;
        for ($i=1; $i <= $etiqueta->quantidade; $i++) { 

            $code = sequenceNextByCodeSepator('ETIQUETA', '');

            // DUPLICAÇÃO
            for ($j=0; $j <= $etiqueta->duplicacao; $j++) { 
                $etiquetasZPL = $etiquetasZPL . $this->etiquetaZPLStr($etiqueta->nome, $etiqueta->data, $code);
            }

            if($i==1){
                $codigosGerados = $code;
            }else{
                $codigosGerados = $codigosGerados . ',' . $code;
            }
        }

        // Make documento diretory
        if (!file_exists(storage_path('app/protocolos'))) {
            mkdir(storage_path('app/protocolos'), 0777, true);
        }

        $path = 'protocolos/protocolo' . $etiqueta->id . '.pdf';

        $this->zplToPDF($etiquetasZPL, storage_path('app/' . $path));

       // PDF::Output(storage_path('app/' . $path), 'F');

        $etiqueta->localizacao = $path;
        $etiqueta->codigos = $codigosGerados;
        $etiqueta->save();
    }

    public function zplToPDF($zpl, $savePath) {

        $curl = curl_init();
        // adjust print density (8dpmm), label width (4 inches), label height (6 inches), and label index (0) as necessary
        curl_setopt($curl, CURLOPT_URL, "http://api.labelary.com/v1/printers/8dpmm/labels/1.5748039999999999x1.181103");
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $zpl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/pdf")); // omit this line to get PNG images back
        $result = curl_exec($curl);

        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            $file = fopen($savePath, "w"); // change file name for PNG images
            fwrite($file, $result);
            fclose($file);
        } else {
            print_r("Error: $result");
        }

        curl_close($curl);
    }

    public function etiquetaZPLStr($nome, $data, $codigo) {

        $zpl = "
           
            ^XA
            ^MMT
            ^PW320
            ^LL240
            ^LS0
            ^FO24,17^GB272,45,1^FS
            ^FPH,1^FT40,51^A0N,22,28^FH\^CI28^FDKAILA^FS^CI27
            ^FPH,1^FT138,47^ACN,18,10^FH\^FD".$nome."^FS
            ^FO24,68^GB272,45,1^FS
            ^FPH,1^FT40,99^A0N,22,28^FH\^CI28^FDDATA^FS^CI27
            ^FPH,1^FT138,96^ACN,18,10^FH\^FD".date('Y-m-d', $data)."^FS
            ^BY3,3,75^FT24,200^BCN,,Y,N
            ^FH\^FD>:I>5".$codigo."^FS
            ^PQ1,0,1,Y
            ^XZ
        ";

        return $zpl;
    }

    public function download($id)
    {
        $etiqueta = EtiquetaProtocolo::find($id);

        if ($etiqueta != null) {
            return Storage::download($etiqueta->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }

    public function view($id)
    {
        $etiqueta = EtiquetaProtocolo::find($id);


        if ($etiqueta != null) {
            return Storage::download($etiqueta->localizacao);
        } else {
            return response()->json(['message' => "Ficheiro não encontrado"], Response::HTTP_BAD_REQUEST);
        }
    }




    // TESTES

    public function teste() {
        $zpl = "^XA
        ~TA000
        ~JSN
        ^LT0
        ^MNW
        ^MTT
        ^PON
        ^PMN
        ^LH0,0
        ^JMA
        ^PR6,6
        ~SD15
        ^JUS
        ^LRN
        ^CI27
        ^PA0,1,1,0
        ^XZ
        ^XA
        ^MMT
        ^PW320
        ^LL240
        ^LS0
        ^FO24,17^GB272,45,1^FS
        ^FPH,1^FT40,51^A0N,22,28^FH\^CI28^FDKAILA^FS^CI27
        ^FPH,1^FT138,47^ACN,18,10^FH\^FDSEDE^FS
        ^FO24,68^GB272,45,1^FS
        ^FPH,1^FT40,99^A0N,22,28^FH\^CI28^FDDATA^FS^CI27
        ^FPH,1^FT138,96^ACN,18,10^FH\^FD29-04-2024^FS
        ^BY3,3,75^FT24,200^BCN,,Y,N
        ^FH\^FD>:I>52400021^FS
        ^PQ1,0,1,Y
        ^XZ
        ";

        $zpl = $zpl . $zpl;
        $zpl = $zpl . $zpl;
        $zpl = $zpl . $zpl;


        $curl = curl_init();
        // adjust print density (8dpmm), label width (4 inches), label height (6 inches), and label index (0) as necessary
        curl_setopt($curl, CURLOPT_URL, "http://api.labelary.com/v1/printers/8dpmm/labels/1.5748039999999999x1.181103");
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $zpl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/pdf")); // omit this line to get PNG images back
        $result = curl_exec($curl);

        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            $file = fopen("label2.pdf", "w"); // change file name for PNG images
            fwrite($file, $result);
            fclose($file);
        } else {
            print_r("Error: $result");
        }

        curl_close($curl);
    }

    public function pdf() {

        $etiqueta = new EtiquetaProtocolo();
        $etiqueta->nome = 'KAILA';
        $etiqueta->quantidade = 1;
        $etiqueta->duplicacao = 1;
        $etiqueta->incluirData  = true;
        $etiqueta->utilizadorId = 'e9e91e6d-1bfe-4fc4-8da1-3eb6d162799b';
        $etiqueta->localizacao = 'protocolos/protocoloTeste.pdf';
        $etiqueta->save();

        $this->gerarEtiquetasPdf($etiqueta);

    }


}