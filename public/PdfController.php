<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Tcpdf\Fpdi;

class PdfController extends Controller
{

    public function showPdf(Request $request)
    {
        // create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Nicola Asuni');
        $pdf->SetTitle('TCPDF Example 052');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 052', PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        /*
        NOTES:
        - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
        - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
        - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
        */

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // set font
        $pdf->SetFont('helvetica', '', 12);

        // add a page
        $pdf->AddPage();

        // print a line of text
        $text = 'This is a <b color="#FF0000">digitally signed document</b> using the default (example) <b>tcpdf.crt</b> certificate.<br />To validate this signature you have to load the <b color="#006600">tcpdf.fdf</b> on the Arobat Reader to add the certificate to <i>List of Trusted Identities</i>.<br /><br />For more information check the source code of this example and the source code documentation for the <i>setSignature()</i> method.<br /><br /><a href="http://www.tcpdf.org">www.tcpdf.org</a>';
        $pdf->writeHTML($text, true, 0, true, 0);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image('images/tcpdf_signature.png', 180, 60, 15, 15, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance(180, 60, 15, 15);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(180, 80, 15, 15);

        // ---------------------------------------------------------

        //Close and output PDF document
        $pdf->Output('example_052.pdf', 'D');

        //============================================================+
        // END OF FILE
        //============================================================+
    }

    public function pdf2(){
        // initiate FPDI
        $pdf = new Fpdi();
        // add a page
        $pdf->AddPage();
        // set the source file
        $pdf->setSourceFile(public_path('new-2.pdf'));
        // import page 1
        $tplId = $pdf->importPage(1);
        // use the imported page and place it at point 10,10 with a width of 100 mm
        $pdf->useTemplate($tplId, 10, 10, 100);

        $pdf->Output();            
    }

    public function pdf3(){
         // initiate FPDI
         $pdf = new Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

         // set document information
         $pdf->SetCreator(PDF_CREATOR);
         $pdf->SetAuthor('Nicola Asuni');
         $pdf->SetTitle('TCPDF Example 052');
         $pdf->SetSubject('TCPDF Tutorial');
         $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
 
         // set default header data
         $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 052', PDF_HEADER_STRING);
 
         // set header and footer fonts
         $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
         $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
 
         // set default monospaced font
         $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
 
         // set margins
         $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
         $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
         $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
 
         // set auto page breaks
         $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
 
         // set image scale factor
         $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
 
         // set some language-dependent strings (optional)
         if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
             require_once(dirname(__FILE__) . '/lang/eng.php');
             $pdf->setLanguageArray($l);
         }
 
         // add a page
         $pdf->AddPage();
         // set the source file
         $pdf->setSourceFile(public_path('funcionario.pdf'));
         // import page 1
         $tplId = $pdf->importPage(1);
         // use the imported page and place it at point 10,10 with a width of 100 mm
         $pdf->useTemplate($tplId, 10, 40, 100);

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image('images/tcpdf_signature.png', 180, 60, 15, 15, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance(180, 60, 15, 15);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(180, 80, 15, 15);

        // ---------------------------------------------------------

 
        $pdf->Output();        
    }

    public function pdf4(){
        // initiate FPDI
        $pdf = new Fpdi();
         // set the source file
        $pageCount = $pdf->setSourceFile(public_path('funcionario.pdf'));

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
        }

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image(public_path('tcpdf_signature.png'), $signaturaX - 50, $signaturaY - 60, 30, 30, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance($signaturaX- 50, $signaturaY- 60, 30, 30, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(25, $signaturaY - 64, 30, 30, 1);

        // ---------------------------------------------------------

        $pdf->Output();        

    }

    public function pdf5(){
        // initiate FPDI
        $pdf = new Fpdi();
         // set the source file
        $pageCount = $pdf->setSourceFile(public_path('funcionario.pdf'));

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

        }

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image(public_path('tcpdf_signature.png'), $signaturaX - 50, $signaturaY - 60, 30, 30, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance($signaturaX- 50, $signaturaY- 60, 30, 30, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(25, $signaturaY - 64, 30, 30, 1);

        // ---------------------------------------------------------
        /*
        // Set the watermark text
        $watermarkText = 'CONFIDENCIAL';

        // Set the watermark font size
        $pdf->SetFont('helvetica', 'B', 70);

        // Set the watermark color
        $pdf->SetTextColor(192, 192, 192);

        $pdf->StartTransform();

        // Rotate the transformation matrix by 45 degrees
        $pdf->Rotate(45);

        
        // Get the page width and height
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        // Calculate the x and y coordinates of the center of the page
        $x = $pageWidth / 2;
        $y = $pageHeight / 2;


        // Write the watermark text to the page
        $pdf->writeHTMLCell(0, 0, -180, $y, "<div style='opacity: 0.1;'>$watermarkText</div>", 0, 1, false, true, 'C', true);
        */
        $pdf->Output();        

    }

    public function pdf6(){
        // initiate FPDI
        $pdf = new Fpdi();
         // set the source file
        $pageCount = $pdf->setSourceFile(public_path('funcionario.pdf'));

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
            
            $pdf->write2DBarcode('www.tcpdf.org', 'PDF417', 80, 90, 0, 30, $style, 'N');
            $pdf->Text(80, 85, 'PDF417 (ISO/IEC 15438:2006)');

        }

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image(public_path('tcpdf_signature.png'), $signaturaX - 50, $signaturaY - 60, 30, 30, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance($signaturaX- 50, $signaturaY- 60, 30, 30, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(25, $signaturaY - 64, 30, 30, 1);

        // ---------------------------------------------------------
       
        return $pdf->Output('new_file.pdf', 'D');       

    }

    public function pdf7(Request $request){
        // initiate FPDI
        $pdf = new Fpdi();
         // set the source file
        $pageCount = $pdf->setSourceFile(public_path('funcionario.pdf'));

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
            
            $pdf->write2DBarcode('www.tcpdf.org', 'PDF417', 80, 90, 0, 30, $style, 'N');
            $pdf->Text(80, 85, 'PDF417 (ISO/IEC 15438:2006)');

        }

        // set certificate file
        $certificate = 'file://'. public_path('tcpdf.crt');

        // set additional information
        $info = array(
            'Name' => 'TCPDF',
            'Location' => 'Office',
            'Reason' => 'Testing TCPDF',
            'ContactInfo' => 'http://www.tcpdf.org',
        );

        // set document signature
        $pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // *** set signature appearance ***

        // create content for signature (image and/or text)
        $pdf->Image(public_path('tcpdf_signature.png'), $signaturaX - 50, $signaturaY - 60, 30, 30, 'PNG');

        // define active area for signature appearance
        $pdf->setSignatureAppearance($signaturaX- 50, $signaturaY- 60, 30, 30, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // *** set an empty signature appearance ***
        $pdf->addEmptySignatureAppearance(25, $signaturaY - 64, 30, 30, 1);

        // ---------------------------------------------------------
       
        return response()->json($pdf->Output('new_file.pdf', 'D'))->setStatusCode(Response::HTTP_OK);


    }
}
