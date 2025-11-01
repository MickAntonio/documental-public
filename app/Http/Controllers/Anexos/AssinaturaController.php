<?php

namespace App\Http\Controllers\Anexos;

use App\Models\Anexo;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Anexos\HistoricoAnexoController;

class AssinaturaController extends Controller
{

    public function assinar(Request $request){

        $anexo = Anexo::find($request->anexo['id']);

        //return Storage::url($anexo->localizacao);
        //return storage_path('app/'.$anexo->localizacao);

        if (!file_exists(storage_path('app/assinaturas'))) {
            mkdir(storage_path('app/assinaturas'), 0777, true);
        }

        // Decode the base64 data

        $format = explode(',', $request->assinatura)[0];
        $base64 = explode(',', $request->assinatura)[1];
        $decodedData = base64_decode($base64);

        $imageName = Auth::user()->id . '.png';

        if($format=='data:image/png;base64'){
            $imageName = Auth::user()->id . '.png';
        } else if($format=='data:image/jpg;base64'){
            $imageName = Auth::user()->id . '.jpg';
        }else if($format=='data:image/jpeg;base64'){
            $imageName = Auth::user()->id . '.jpeg';
        }

        file_put_contents(storage_path('app/assinaturas/' . $imageName), $decodedData);
        
        // initiate FPDI
        $pdf = new Fpdi();
         // set the source file
        //$pageCount = $pdf->setSourceFile(Storage::download($anexo->localizacao));
        $pageCount = $pdf->setSourceFile(storage_path('app/'.$anexo->localizacao));

        $signaturaX = 0;
        $signaturaY = 0;

        // Loop through each page and add it to the TCPDF document
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $templateId = $pdf->importPage($pageNumber);

            $size = $pdf->getTemplateSize($templateId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, array($size['width'], $size['height']));
            $pdf->useTemplate($templateId);

            if($pageNumber==1){
                $signaturaX = $size['width'];
                $signaturaY = $size['height'];
            }

            // ---------------------------------------------------------
            /*
            // Set the watermark image
            */
            if($orientation=='P'){
                $pdf->Image(public_path('confidencial.png'), 20, 100, 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, true);
            }else{
                $pdf->Image(public_path('confidencial.png'), 0, 20, 0, 0, '', '', '', false, 300, '', false, false, 0, false, false, true);
            }

            // set style for barcode
            $style = array(
                'border' => 2,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, //array(255,255,255)
                'module_width' => 1, // width of a single module in points
                'module_height' => 1 // height of a single module in points
            );
            
            //$pdf->write2DBarcode('www.tcpdf.org', 'PDF417', 80, 90, 0, 30, $style, 'N');
            //$pdf->Text(80, 85, 'PDF417 (ISO/IEC 15438:2006)');

        }

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Assinatura Eletrónica - '.$anexo->nome,
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image(storage_path('app/assinaturas/'.$imageName), 25, $signaturaY - 70, 0, 30, 'PNG', '','', true);
        // $pdf->Image(storage_path('app/assinaturas/'.$imageName), 50, 50, 101, 100, 'PNG', '','', true);

        // define active area for signature appearance
        //$pdf->setSignatureAppearance(50, 50, 101, 100, 1);
        $pdf->setSignatureAppearance(25, $signaturaY - 70, 60, 30, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
       //  $pdf->addEmptySignatureAppearance($signaturaX - 50, $signaturaY - 70, 30, 30, 1);
       // $pdf->addEmptySignatureAppearance(169, 299, -115, -32, 1);
        //$pdf->addEmptySignatureAppearance(25, $signaturaY - 64, 30, 30, 1);

        // ---------------------------------------------------------

        $ficheiro = $pdf->Output('new_file.pdf', 'S');

         // History
        $anexoHistorico = new HistoricoAnexoController();
        $res = $anexoHistorico->store($anexo, Auth::user()->id);

        // Save the stream to a file in the storage/app/uploads directory
        file_put_contents(storage_path('app/' . $anexo->localizacao), $ficheiro);

        $anexo->versao      = $anexo->versao + 0.5;
        $anexo->editadoPor  = Auth::user()->name;
        $anexo->observacao  = 'Assinado por '.Auth::user()->name;
        $anexo->save();
       
        return $ficheiro;         

    }

}