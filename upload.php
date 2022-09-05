<?php
include "index.html";
require_once('consultas_db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


mb_internal_encoding("UTF-8");


function utf8_converter($array)
{
    array_walk_recursive($array, function(&$item, $key){
        $current_encoding = mb_detect_encoding($item, 'auto');
        $item = iconv($current_encoding, 'UTF-8', $item);
    });
 
    return $array;
}

$name_subida = $_FILES['csv']['name'];
$name = explode('.', $name_subida);
$ext = strtolower(end($name));
$tmpName = $_FILES['csv']['tmp_name'];

$lista = $_POST['numbers'];

$a_idcuentas = array();
$row_count = 0;
$cantidades = array();
$idcuenta = array();
$validados = array();
$invalidos = array();
$tabla_telefonos = array();
$patron = "/^[[:digit:]]+$/";

//Consulta::borrar_tabla_temporal ();

echo "<div>";

if ($ext === 'csv'){

    $csv = $array = array_map(function($d) {
                         return str_getcsv($d, ";", "\"");
                        }, file($tmpName));

    $csv = utf8_converter($csv);

    //elimino el primer elemento de la columna si no es un idcuenta (number o stringnumber);
    $primer_celda =  $csv[0];

    if (preg_match($patron, $primer_celda[0]) == FALSE) {
        //print_r($csv[0]);
        unset($csv[0]);
    };

    foreach ($csv as $row) {
        array_push($idcuenta, $row[0]);
        $row_count ++;

    };

    //cuento cantidad de cuentas y las meto en cantidades
    array_push($cantidades, $row_count);

    $idcuentas_string="'".implode("', '",$idcuenta)."'";
     //echo $idcuentas_string;
     
} else if (!empty($lista )){

    $idcuentas_string = str_replace(array("\r\n", "\n\r", "\r", "\n"), "','", $lista);
    $idcuentas_string = preg_replace("/[^0-9]/i", " ", $idcuentas_string);
    $idcuentas_string = "'".(preg_replace("/\s/", "','", $idcuentas_string))."'";

    $a_idcuentas = explode(",", $idcuentas_string);
    $a_contador = count(array_unique($a_idcuentas));

    array_push($cantidades, $a_contador);
} else{
    //no es un .csv y la lista es incorrecta
    header('Location: http://localhost/campaign/index.html');
};

    $session = Consulta::buscar_session();
    $session = $session+1;

    $telefonos_encontrados = Consulta::buscar_telefonos($idcuentas_string);

    while ($record = sqlsrv_fetch_array($telefonos_encontrados, SQLSRV_FETCH_ASSOC)){ 
        $phone = $record['Telefonos'];
        if (in_array($phone, array_column($tabla_telefonos, 'Telefonos')) == false ){

            $tabla_telefonos[]=$record;
        }
    };

    if (empty($tabla_telefonos)) {
        echo "Consulta a DB ha fallado";
    } else {
        //print_r($tabla_telefonos);
        //cuento cantidad de registros=teléfonos y las meto en cantidades
        array_push($cantidades, count($tabla_telefonos));
        

        //chequeo si el telefono válido o inválido
        $i = 0;
        $cant_val = 0;
        $cant_inval = 0;
       
        foreach ($tabla_telefonos as $arr) {
            if (preg_match($patron, $arr['Telefonos'])){
                $invalido = Consulta::buscar_tel_validados($arr['Telefonos'],"telefonos_invalidos","telefono");
                if (!Empty($invalido)){ 
                    array_push($invalidos, $arr);
                    unset($tabla_telefonos[$i]);
                    $cant_inval ++;
                } else {
                    $valida = Consulta::buscar_tel_validados($arr['Telefonos'],"telefonos_validos","telefono");
                    if (!Empty($valida)) {
                        array_push($validados, $arr);
                        unset($tabla_telefonos[$i]);
                        $cant_val ++;
                    };
                };
                $i ++;
            }
        };

        //lleno la tabla temporal
        Consulta::llenar_tabla_temporal($tabla_telefonos, $validados, $invalidos, $session);

        //agrego cantidad tel validos e invalidos
        array_push($cantidades, $cant_val, $cant_inval);
        //print_r($cantidades);
        $por_chequear = $cantidades[1]-$cantidades[2]-$cantidades[3];
        if($por_chequear == 0){
            $por_chequear = '-';
        }
        array_push($cantidades, $por_chequear);
        if ($cantidades[2] == 0){
            $cantidades[2] = '-';
        }
        if ($cantidades[3] == 0){
            $cantidades[3] = '-';
        }
        
    };
 
echo "</div>";


?>


<!DOCTYPE html>

<section id="campain-css-result">
        <div class="container-fluid" id="campain-css">
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" id="campain-col-2">
                    <h2> Resumen de  tu campaña </h2> 
                    <p>#idcampaña: <a id="nro_session"><?php echo $session ?></a></p> <!-- <input type="button" value="Recalcular" id="recargar" onclick=recargar()></input> -->
                    <p> Cantidad de cuentas: <a id="cuentas"><strong> <?php echo $cantidades[0] ?></strong></a></p>
                    <p> Cantidad de celulares: <a id="celulares"><strong><?php echo ($cantidades[1]) ?></strong></a></p>
                    <p> Cantidad de celulares sin chequear: <a id="cant_chequear"><?php echo ($cantidades[4]) ?></a><a><input type="button" value="Descargar" id="donwload_nros" onclick=download_para_chequear()></input></a></p>
                    <p> Cantidad de celulares validados: <a id="cant_val"><?php echo ($cantidades[2]) ?></a><a> <input type="button" value="Eliminar" id="validados" onclick=cambiar_validados()></input> <input type="button" value="Descargar" id="donwload_val" onclick=download_validos()></input></a></p>
                    <p> Cantidad de celulares inválidos: <a id="cant_inv"><?php echo ($cantidades[3]) ?></a><a> <input type="button" value="Eliminar" id="invalido" onclick=cambiar_invalidos()></input> <input type="button" value="Descargar" id="download_inval" onclick=download_invalidos()></input></a></p>
                    <p> Nombre empresa a mostrar: <input type="text" id="empresa-cliente"></input> </p>
                    <div id="boton-centrado">
                        <input type="button" name="final" value="Descargar campaña" onclick=getOutput()></input>
                    </div>
                    <a href="archivos/camp.csv" download="campaign.csv" style="visibility:hidden;" id="descargar"></a>
                    <a href="archivos/para_chequear.csv" download="para_chequear.csv" style="visibility:hidden;" id="descargarchequear"></a>
                    <a href="archivos/Validado.csv" download="Validado.csv" style="visibility:hidden;" id="descargarval"></a>
                    <a href="archivos/Invalido.csv" download="Invalido.csv" style="visibility:hidden;" id="descargarinv"></a>
                    <div class="output"></div>  
                </div>
            </div>
        </div>
</section>

<script type="text/javascript" src="js.js"></script>
<!-- <script src="https:/code.jquery.com/jquery-3.6.0.min.js"></script> -->


</html>
