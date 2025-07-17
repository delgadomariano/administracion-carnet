 

<?php
    ini_set("display_errors", "1");
    //error_reporting(E_ALL & ~E_NOTICE);
    //include('config.php');
    use setasign\Fpdi\Fpdi;

    require_once('fpdf181/fpdf.php'); 
    require_once('fpdi2/src/autoload.php'); 
    require_once('mis_variables_pdf.php'); 

    $pdf = new FPDI(); 

    # Pagina 1
    $pdf->AddPage('P'); 
    $tipo =  $_REQUEST['tipo']; 
    if ($tipo=='misionero'){
        $pdf->setSourceFile('Files_Pdf/carnet-adelante-m.pdf'); 
        $color = 0;
    }
    else{
        $pdf->setSourceFile('Files_Pdf/carnet-adelante.pdf');
        $color = 255; 
    }    
    
    $tplIdx = $pdf->importPage(1); 
    $pdf->useTemplate($tplIdx); 

    $top = 15.5;
    $der = 37.5;
    
    if ($Imagen<>''){
        $pdf->Image($Imagen,$der,$top,26.1,24.9);  
    }

    $top = $top + 25;
    $der = $der - 2;
    
  
    $pdf->SetFont('helvetica', 'B', '10'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    $nombreEncargadoUtf8 = utf8_decode($nombreEncargado);
    $pdf->Write(10,$nombreEncargadoUtf8);

    $top = $top + 3;

    $pdf->SetFont('helvetica', '', '9'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    $pdf->Write(10,'ID:' . $Numero );

    $top = $top + 3;
    
    $pdf->SetFont('helvetica', '', '9'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    $pdf->Write(10,'DNI:' . $nroDocumento);   
    
    $top = $top + 3;

    $pdf->SetFont('helvetica', '', '9'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    if ($tipo=='misionero'){
         $pdf->Write(10,'Instructor');  
    }
    else{
        $pdf->Write(10,$Cargo);
    }   
      
    $pdf->AddPage('P'); 
    if ($tipo=='misionero'){
        $pdf->setSourceFile('Files_Pdf/carnet-atras-m.pdf'); 
    }
    else{
        $pdf->setSourceFile('Files_Pdf/carnet-atras.pdf'); 
    }   
    $tplIdx = $pdf->importPage(1); 
    $pdf->useTemplate($tplIdx);  
    //
    $d=strtotime("+2 Years"); 
    //
    $top = $top+3;
    $pdf->SetFont('helvetica', '', '7'); 
    $pdf->SetXY($der+7,$top);
    $pdf->SetTextColor(0,0,0);
    if ($tipo=='misionero'){
         $pdf->Write(10,'Fecha de emision '. date("d-m-Y").' - Vence '. date("d-m-Y", $d));  
    }
    else{
        $pdf->Write(10,'Fecha de emision '. date("d-m-Y").' - Vence '. date("d-m-Y", $d));
    }   
    //  
    $pdf->Output('Files_Tmp/' . $nroDocumento . '.pdf', 'F'); //SALIDA DEL PDF
    //    $pdf->Output('original_update.pdf', 'F');
    //    $pdf->Output('original_update.pdf', 'I'); //PARA ABRIL EL PDF EN OTRA VENTANA
    //	  $pdf->Output('original_update.pdf', 'D'); //PARA FORZAR LA DESCARGA
  
    echo "<script> window.location='Files_Tmp/" . $nroDocumento . ".pdf' </script>";
 
?>