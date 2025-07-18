 

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
    $cargo =  $_REQUEST['cargo']; 
    $ambito =  $_REQUEST['ambito']; 
    $nro_filial =  $_REQUEST['nro_filial']; 

    $pdf->setSourceFile('Files_Pdf/carnet.pdf'); 
    $color = 0;
        
    $tplIdx = $pdf->importPage(1); 
    $pdf->useTemplate($tplIdx); 


     $top = 29;
    $der = 45;
  
    $pdf->SetFont('helvetica', 'B', '14'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    $nombreEncargadoUtf8 = utf8_decode($nombreEncargado);
    $pdf->Write(10,$nombreEncargadoUtf8);
 
    $top = $top + 8;
    
    $pdf->SetFont('helvetica', '', '12'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
    $pdf->Write(10,'DNI:' . $nroDocumento);   
    
    $top = $top + 6;

    $pdf->SetFont('helvetica', '', '12'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color); 
    $pdf->Write(10,$cargo);

     $top = $top + 6;
   
    $pdf->SetFont('helvetica', '', '12'); 
    $pdf->SetXY($der,$top);
    $pdf->SetTextColor($color,$color,$color);
     if($ambito=='Filial'){
        $ambito = $ambito . ' Nro. ' . $nro_filial;
    } 
    $pdf->Write(10,$ambito);
      
         $top = $top + 66;
    $der =  $der + 43;
    
    if ($Imagen<>''){
        $pdf->Image($Imagen,$der+1,$top+1,27,27);  
    }
    
    //
    $d=strtotime("+2 Years"); 
    //
    $top = $top+20;
     $der =  $der - 35;
    $pdf->SetFont('helvetica', '', '9'); 
    $pdf->SetXY($der+7,$top);
    $pdf->SetTextColor(0,0,0);
   
        $pdf->Write(10,''. date("d-m-Y", $d));
     
    //  
    $pdf->Output('Files_Tmp/' . $nroDocumento . '.pdf', 'F'); //SALIDA DEL PDF
    //    $pdf->Output('original_update.pdf', 'F');
    //    $pdf->Output('original_update.pdf', 'I'); //PARA ABRIL EL PDF EN OTRA VENTANA
    //	  $pdf->Output('original_update.pdf', 'D'); //PARA FORZAR LA DESCARGA
  
    echo "<script> window.location='Files_Tmp/" . $nroDocumento . ".pdf' </script>";
 
?>