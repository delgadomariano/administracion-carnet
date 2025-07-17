<?php
//include('config.php');

const TEMPIMGLOC = 'tempimg.jpg';

$id = "2";
$sqlVariables = ("SELECT * FROM mis_variables WHERE id='".$id."'");
//$queryVar  = mysqli_query($con, $sqlVariables);
//$MiDataVar = mysqli_fetch_array($queryVar);

 
$url = 'http://ac.gnosis.is/api/GAPP/GETMIEMBRO/gapp/' . $_REQUEST['nro_documento'] ;
$json = file_get_contents($url);
$jo = json_decode($json);


$nombreEncargado =   $jo[0]->name; 
$Numero 		 =   $jo[0]->registration; 
$codCargo =  $jo[0]->priestType;
$esObispo =  $jo[0]->isBishop;
if ($codCargo == '') {
  $Cargo = 'Grey';
}
else
{
  $Cargo = $codCargo=='S' ? 'Sacerdote' : 'Isis' ; 
}
if($esObispo=='SI'){
  $Cargo = 'Obispo';
}

$nroDocumento    =   $_REQUEST['nro_documento'];

//$Imagen = 'ana.jpg'; //getImage($jo[0]->img_imagen);   
 //print($nombreEncargado);
 $findme   = 'data:image/jpeg;base64,';
 $pos = strpos($jo[0]->img_imagen, $findme);
 if ($pos === false) {
  $Imagen =  $jo[0]->img_imagen ;
 } else {
  $Imagen = getImage($jo[0]->img_imagen,  $nroDocumento);
 }


// print($Imagen  );
/* VARIABLES */
 


function getImage($imagenes,  $nroDocumento){ 
  $imagenEnBase64 =  str_replace('data:image/jpeg;base64,', '', $imagenes);   // variable de imagen en b64
  //print($imagenEnBase64  );
  $filename = 'Files_Img/' . $nroDocumento.'.jpg'; // declaramos el nombre de la imagen segun el art_codigo
  $rutaImagenSalida = __DIR__ . '/'.$filename.''; // difinimos la ruta donde se va a cargar la imagen
  $imagenBinaria = base64_decode($imagenEnBase64); // decodificamos la imagen en b64
  $bytes = file_put_contents($rutaImagenSalida, $imagenBinaria); // subimos la imagen
  return $rutaImagenSalida;
  }
?>