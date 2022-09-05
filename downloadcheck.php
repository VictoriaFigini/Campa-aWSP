<?php
require_once('consultas_db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 
 
mb_internal_encoding("UTF-8");

$tipo = $_GET['Val'];
$tabla_telefonos = array();
$session = $_GET['Session'];
$ruta = "";

//echo " Tipo ".$tipo;

function utf8_converter($array){
    foreach($array as $item){

        $item = utf8_encode($item);

    };
 
    return $array;
}

if ($tipo != ""){
    //print "Entra a if";

    $tabla_telefonos = Consulta::traer_chequeados($tipo, $session);
    
    if(!empty($tabla_telefonos)){
        //print "tabla NO vacia ";
        //print_r($tabla_telefonos);
       

        $delimiter = ";";
        $filename = $tipo.".csv";
        
        $ruta = " Tipo: ".$tipo." session: ".$session;
        //print " RUTA ".$ruta;
        //header('Content-Encoding: UTF-8');
        //header('Content-Type: text/csv; charset=utf-8');
        //header('Content-Disposition: attachment; filename: "'.$filename.'"');

        //create a file pointer
        $f = fopen($_SERVER['DOCUMENT_ROOT'].'/campaign/archivos/'.$filename, 'w');


        if ($tipo =="para_chequear"){

            foreach ($tabla_telefonos as $row){
                $telcheck = "549".strval($row['Telefonos']);
                $lineData = array($telcheck, "");
                //print_r($lineData);
                fputcsv($f, $lineData, $delimiter);
            }
        }else{
            //print("VAL-INVAL ".$tipo);
            //set column headers
            $fields = array('IDCuenta',	'Telefono');
            fputcsv($f, $fields, $delimiter);
            foreach ($tabla_telefonos as $row) {
                //output each row of the data, format line as csv and write to file pointer
                $lineData = array($row['IDCuenta'], $row['Telefonos']);
                $lineData = utf8_converter($lineData);
                //print_r($lineData);
                fputcsv($f, $lineData, $delimiter);
            };
        }
        fclose($f);
        echo json_encode(2);
    
exit();
    }
} else {
    echo json_encode(1);
}


?>