<?php
// include "index.html";
require_once('consultas_db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$session = $_GET['Session'];
$retorno = array();

$retorno = Consulta::recargar($session);

$para_chequear =$retorno['Telefonos']-($retorno['Validados']+$retorno['Invalidos']);
if ($retorno['Validados'] == 0){
    $val = "-";
} else {
    $val = $retorno['Validados'];
}
if ($retorno['Invalidos'] == 0){
    $inv = "-";
} else {
    $inv = $retorno['Invalidos'];
}
if ($para_chequear == 0){
    $para_chequear = "-";
} 
$datos = array($para_chequear, $val, $inv);
//print_r($datos);

echo json_encode($datos);
