<?php
require_once('consultas_db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 
 
mb_internal_encoding("UTF-8");

$nombremostrar = $_GET['Cliente'];
$con_validos = $_GET['Val'];
$con_invalidos = $_GET['Inv'];
$tabla_telefonos = array();
$session = $_GET['Session'];
$ruta = "";

echo "cliente ".$nombremostrar." - invalidos ".$con_invalidos." - validos ".$con_validos." server ".$_SERVER['DOCUMENT_ROOT'];;

function utf8_converter($array){
    array_walk_recursive($array, function(&$item, $key){
        $current_encoding = mb_detect_encoding($item, 'auto');
        $item = iconv($current_encoding, 'Windows-1252', $item);
    });
 
    return $array;
}

if ($nombremostrar != ""){
    echo "Entra a if ".$nombremostrar;

    $conectoresNombreConNombre = array("di", "de", "da", "dos", "lo", "los", "la", "las", "del", "y", "san", "d", "o");
    $tabla_telefonos = Consulta::traer_tabla_temporal($con_validos, $con_invalidos, $session);
    
    if(!empty($tabla_telefonos)){

        $delimiter = ";";
        $filename = "camp.csv";
        
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename: "'.$filename.'"');

        //create a file pointer
        $f = fopen($_SERVER['DOCUMENT_ROOT'].'/campaign/archivos/'.$filename, 'w');

        $ruta = $_SERVER['DOCUMENT_ROOT'].'/campaign/archivos/'.$filename." variables: cliente ".$nombremostrar." - invalidos ".$con_invalidos." - validos ".$con_validos." - Sesion: ".$session;

        //set column headers
        $fields = array('IDCuenta',	'Apellido y Nombre', 'Mensaje', 'Id usuario', 'Telefono');
        fputcsv($f, $fields, $delimiter);

        foreach ($tabla_telefonos as $row) {
            $cadena_nombre = str_word_count($row['RazonSocial'], 1);
            //print_r($cadena_nombre);

            if (count($cadena_nombre) > 2){
                $nombre = mb_strtolower($cadena_nombre[0], 'UTF-8');
                if (in_array($nombre, $conectoresNombreConNombre)){
                    $nombre = mb_strtolower($cadena_nombre[1], 'UTF-8');
                    if (in_array($nombre, $conectoresNombreConNombre)){
                        $nombre = mb_strtolower($cadena_nombre[3], 'UTF-8');
                    }else{
                        $nombre = mb_strtolower($cadena_nombre[2], 'UTF-8');   
                    };
                } else {
                    $nombre = mb_strtolower($cadena_nombre[1], 'UTF-8');
                };
            } else{
                $nombre = mb_strtolower($cadena_nombre[1], 'UTF-8');
            }
            $nombre = ucfirst($nombre);

            if (str_word_count($row['Nombre'], 0) > 1){
                $cadena_ejecutivo = str_word_count($row['Nombre'], 1);
                $nombre_ejecutivo = $cadena_ejecutivo[0];
            } else {
                $nombre_ejecutivo = $row['Nombre'];
            }


            $msj = array("$nombre, buenos días. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto para hablar?", 
            "Buen día $nombre. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto para hablar?",
            "Hola $nombre, Buen día. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto para hablar?",
            "$nombre, Buen dia. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto?",
            "Buen dia $nombre. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto?",
            "Hola $nombre, Buen dia. Mi nombre es $nombre_ejecutivo, de $nombremostrar. Tiene un minuto?",
            "$nombre, Como le va?. Habla, $nombre_ejecutivo. Me gustaria hablar con ud, por su cuenta en $nombremostrar. En que momento podemos hablar?",
            "$nombre buen dia. Me avisa cuando tenga un minuto? Le escribe $nombre_ejecutivo de $nombremostrar",
            "Buen dia $nombre. Me avisa cuando tenga un minuto? Le escribe $nombre_ejecutivo de $nombremostrar",
            "Hola $nombre. Me avisa cuando tenga un minuto? Le escribe $nombre_ejecutivo de $nombremostrar");
            
            //$msj=utf8_converter($msj);
            $mensaje = array_rand($msj);
        
        //output each row of the data, format line as csv and write to file pointer
            $lineData = array($row['IDCuenta'], $row['RazonSocial'], $msj[$mensaje], $row['IDUsuario'], $row['Telefonos']);
            $lineData = utf8_converter($lineData);
            print_r($lineData);
            fputcsv($f, $lineData, $delimiter);
        };

        Consulta::borrar_tabla_temporal($session);
        fclose($f);
    
    //move back to beginning of file
    //fseek($f, 0);
    
    //set headers to download file rather than displayed
    //header('Content-Type: text/csv; charset=UTF-8');
    //header('Content-Disposition: attachment; filename="' . $filename . '";');
    //readfile('archivos'.$filename);
    //output all remaining data on a file pointer
    //fpassthru($f);
    //print('archivos/camp.csv');
    }
    echo json_encode(2);
exit();

} else {
    echo json_encode(1);
}


?>
