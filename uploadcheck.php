<?php
// include "index.html";
require_once('consultas_db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key){
        if(!mb_detect_encoding($item, 'utf-8', true)){
                $item = utf8_encode($item);
        }
    });
 
    return $array;
}

$name_subida = $_FILES['csv']['name'];
$name = explode('.', $name_subida);
$ext = strtolower(end($name));
$tmpName = $_FILES['csv']['tmp_name'];
$contador = 0;

//var_dump($tmpName);
$telefonos = array();

echo "<div>";
if ($ext === 'csv' && !empty($_POST['tipo'])){
    $valido = $_POST['tipo'];
    //echo $valido;
    $csv = $array = array_map(function($d) {
                        return str_getcsv($d, ";", "\"");
                        }, file($tmpName));

    $csv = utf8_converter($csv);


    foreach ($csv as $row) {
        $tel = str_replace("/[^0-9]/", "", $row[0]);
        $tel = substr(strval($tel), 3, 10);
        array_push($telefonos, $tel);
        $contador += 1;
    };

    echo $contador;
    //$telefonos_string="'".implode("', '",$telefonos)."'";
    //echo $idcuentas_string;
    $retorno = Consulta::cargar_telefonos_checkeados($telefonos, $valido);
    if ($retorno == "ok"){
        
        echo '<script language="javascript">alert("Teléfonos guardados");</script>';
        return json_encode($contador);

    } else{
        echo '<script language="javascript">alert("Error en la carga. Revise los datos e intente nuevamente.");</script>';
        return json_encode(0);
    }

}else{
    echo '<script language="javascript">alert("Datos incorrectos");</script>';
    //header("Location: http://localhost/campaign/index.html");
    exit();
};

