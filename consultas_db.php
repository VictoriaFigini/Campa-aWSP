<?php
require_once('db.php');
//require_once('db_sqlite.php');

class Consulta {

	public static function buscar_telefonos($idcuenta){
		global $bd;
		return $bd-> buscar_por_sql("SELECT cuentas.IDCuenta, tel.Telefonos, entidades.RazonSocial, CASE WHEN usuarios.IDUsuario = '0' THEN '8' ELSE usuarios.IDUsuario END AS IDUsuario,
        CASE WHEN usuarios.Nombre = 'Sin asignar' THEN 'Rosario' ELSE usuarios.Nombre END AS Nombre, tel.Validado, tel.Favorito, tel.IDTelefono   
        FROM Cuentas cuentas INNER JOIN Entidades ON entidades.IDEntidad = cuentas.IDEntidad INNER JOIN Usuarios ON usuarios.IDUsuario = cuentas.IDEjecutivo 
        Right Join 
        (SELECT entidades.MatriculaUnica, LTRIM(telefonos.Prefijo + telefonos.Numero)  AS Telefonos, entidades.RazonSocial, CASE WHEN usuarios.IDUsuario = '0' THEN '8' ELSE usuarios.IDUsuario END AS IDUsuario, CASE WHEN usuarios.Nombre = 'Sin asignar' THEN 'Rosario' ELSE usuarios.Nombre END AS Nombre, CASE WHEN telefonos.IDTipoDeTelefono = '9' THEN 'OK' ELSE '' END AS Validado, telefonos.Favorito, telefonos.IDTelefono    
        FROM Telefonos telefonos INNER JOIN Entidades ON entidades.IDEntidad = telefonos.IDEntidad INNER JOIN Cuentas ON  cuentas.IDEntidad = entidades.IDEntidad INNER JOIN Usuarios ON usuarios.IDUsuario = cuentas.IDEjecutivo  
        WHERE telefonos.Activo = 'S' AND entidades.MatriculaUnica <> '0' AND entidades.MatriculaUnica <> '' AND telefonos.Prefijo LIKE '%[^/-\s\WA-Za-z]%' AND telefonos.Numero LIKE '%[^/-\s\WA-Za-z]%' GROUP BY entidades.MatriculaUnica, entidades.RazonSocial, usuarios.IDUsuario, usuarios.Nombre, telefonos.Prefijo + telefonos.Numero, telefonos.IDTipoDeTelefono, telefonos.Favorito, telefonos.IDTelefono  
        HAVING LEN(LTRIM(telefonos.Prefijo + telefonos.Numero)) = 10 UNION  
        SELECT entidades.MatriculaUnica, LTRIM(telefonos.DatoSinFormato) AS Telefonos, entidades.RazonSocial, CASE WHEN usuarios.IDUsuario = '0' THEN '8' ELSE usuarios.IDUsuario END AS IDUsuario, CASE WHEN usuarios.Nombre = 'Sin asignar' THEN 'Rosario' ELSE usuarios.Nombre END AS Nombre, CASE WHEN telefonos.IDTipoDeTelefono = '9' THEN 'OK' ELSE '' END AS Validado, telefonos.Favorito, telefonos.IDTelefono  
        FROM Telefonos telefonos INNER JOIN Entidades ON entidades.IDEntidad = telefonos.IDEntidad INNER JOIN Cuentas ON  cuentas.IDEntidad = entidades.IDEntidad INNER JOIN Usuarios ON usuarios.IDUsuario = cuentas.IDEjecutivo 
        WHERE telefonos.Activo = 'S' AND entidades.MatriculaUnica <> '0' AND entidades.MatriculaUnica <> '' AND telefonos.DatoSinFormato LIKE '%[^/-\s\WA-Za-z]%' GROUP BY entidades.MatriculaUnica, entidades.RazonSocial, usuarios.IDUsuario, usuarios.Nombre, telefonos.DatoSinFormato, telefonos.IDTipoDeTelefono, telefonos.Favorito, telefonos.IDTelefono  
        HAVING LEN(LTRIM(telefonos.DatoSinFormato)) = 10 AND telefonos.DatoSinFormato Is Not Null) tel ON tel.MatriculaUnica = entidades.MatriculaUnica 
        WHERE cuentas.Activa = 'S' AND IDCuenta IN ($idcuenta) AND LEN(tel.Telefonos) = 10 
        UNION SELECT cuentas.IDCuenta, tel.DatoSinFormato AS Telefonos, entidades.RazonSocial, CASE WHEN usuarios.IDUsuario = '0' THEN '8' ELSE usuarios.IDUsuario END AS IDUsuario, CASE WHEN usuarios.Nombre = 'Sin asignar' THEN 'Rosario' ELSE usuarios.Nombre END AS Nombre, CASE WHEN tel.IDTipoDeTelefono = '9' THEN 'OK' ELSE '' END AS Validado, tel.Favorito, tel.IDTelefono   
        FROM Telefonos tel INNER JOIN Cuentas ON  tel.IDEntidad = cuentas.IDEntidad INNER JOIN Entidades ON entidades.IDEntidad = cuentas.IDEntidad INNER JOIN Usuarios ON usuarios.IDUsuario = cuentas.IDEjecutivo 
        WHERE cuentas.Activa = 'S' AND tel.Activo = 'S' AND IDCuenta IN ($idcuenta) AND LEN(tel.DatoSinFormato) = 10  
        UNION SELECT cuentas.IDCuenta, REPLACE(LTRIM((telefonos.Prefijo + telefonos.Numero)),'0','') AS Telefonos, entidades.RazonSocial, CASE WHEN usuarios.IDUsuario = '0' THEN '8' ELSE usuarios.IDUsuario END AS IDUsuario, CASE WHEN usuarios.Nombre = 'Sin asignar' THEN 'Rosario' ELSE usuarios.Nombre END AS Nombre, CASE WHEN telefonos.IDTipoDeTelefono = '9' THEN 'OK' ELSE '' END AS Validado, telefonos.Favorito, telefonos.IDTelefono  
        FROM Telefonos telefonos INNER JOIN Cuentas ON  telefonos.IDEntidad = cuentas.IDEntidad INNER JOIN Entidades ON entidades.IDEntidad = cuentas.IDEntidad INNER JOIN Usuarios ON usuarios.IDUsuario = cuentas.IDEjecutivo 
        WHERE cuentas.Activa = 'S' AND telefonos.Activo = 'S' AND IDCuenta IN ($idcuenta) AND telefonos.Prefijo LIKE '%[^-\s\WA-Za-z]%' AND telefonos.Numero LIKE '%[^/-\s\WA-Za-z]%' AND LEN(REPLACE(LTRIM((telefonos.Prefijo + telefonos.Numero)),'0','')) = 10  ");

	}
	
	public static function buscar_tel_validados($parametro,$tabla,$nombre){
		//global $dblite;
        $conexion = new SQLite3('cam.db');
        $resultado = $conexion->query("SELECT * FROM $tabla WHERE $nombre = $parametro");
        $row = $resultado->fetchArray();
        //echo "consulta 1";
        //print_r($row);
        return $row;
    }

    public static function buscar_session(){
        $conexion = new SQLite3('cam.db');
        $resultado = $conexion->querySingle("SELECT max(nro) FROM sessions");
        return $resultado;
    }

    public static function llenar_tabla_temporal($tabla_telefonos, $validos, $invalidos, $session){
        $conexion = new SQLite3('cam.db');
        $patern = array("'", ".", "/", ",");
        //print_r($validos);
        $conexion->exec("INSERT INTO sessions (nro) VALUES ($session);");
        foreach ($tabla_telefonos as $arr){
            $tel = intval($arr['Telefonos']);
            $nombre = str_replace($patern, "", $arr['RazonSocial']);
            $conexion->exec("INSERT INTO camp_temporal (IDCuenta, Telefonos, RazonSocial, IDUsuario, Nombre, Session) VALUES ($arr[IDCuenta], $tel, '$nombre', $arr[IDUsuario], '$arr[Nombre]', $session)");
        };
        foreach ($validos as $arr){
            $tel = intval($arr['Telefonos']);
            $nombre= str_replace($patern, "",$arr['RazonSocial']);
            $conexion->exec("INSERT INTO camp_temporal (IDCuenta, Telefonos, RazonSocial, IDUsuario, Nombre, Validado, Session) VALUES ($arr[IDCuenta], $tel, '$nombre', $arr[IDUsuario], '$arr[Nombre]', 'S', $session);");
        };
        //print_r($invalidos);
        foreach ($invalidos as $arr){
            $tel = intval($arr['Telefonos']);
            $nombre= str_replace($patern, "",$arr['RazonSocial']);
            $conexion->exec("INSERT INTO camp_temporal (IDCuenta, Telefonos, RazonSocial, IDUsuario, Nombre, Invalido, Session) VALUES ($arr[IDCuenta], $tel, '$nombre', $arr[IDUsuario], '$arr[Nombre]', 'S', $session);");
        };
    }
    

    public static function traer_tabla_temporal($con_validos, $con_invalidos, $session){
        //global $dblite;

        if($con_validos=="-" && $con_invalidos=="-" || $con_validos=='-' && $con_invalidos=='-'){
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT * FROM camp_temporal WHERE Session = $session AND Invalido IS NOT 'S' AND Validado IS NOT 'S';");
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        } elseif ($con_invalidos == "-" || $con_invalidos == '-'){
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT * FROM camp_temporal WHERE Session = $session AND Invalido IS NOT 'S';");  
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        } elseif ($con_validos =="-" || $con_validos =='-'){
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT * FROM camp_temporal WHERE Session = $session AND Validado IS NOT 'S';");
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        } else {
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT * FROM camp_temporal WHERE Session = $session;");
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        };

        $multiArray = array(); //array to store all rows

        while($resultArray !== false){
            array_push($multiArray, $resultArray); //insert all rows to $multiArray
            $resultArray = $result->fetchArray(SQLITE3_ASSOC); //read next row
        }

        unset($resultArray); //unset temporary variable

        //now all rows are now in $multiArray
        //print_r($multiArray);
        

        return $multiArray;
    }

    public static function borrar_tabla_temporal($session) {
        $conexion = new SQLite3('cam.db');
        $conexion->exec("DELETE FROM camp_temporal WHERE Session = $session;");

    }

    public static function cargar_telefonos_checkeados($telefonos, $tipo){
        $fecha = date('d-m-Y');
        $conexion = new SQLite3('cam.db');
        //print_r($validos);
        if ($tipo == "validos"){
            foreach ($telefonos as $tel){
                $tel = intval($tel);
                $conexion->exec("INSERT or REPLACE INTO telefonos_validos (telefono, fecha) VALUES ($tel, '$fecha'); UPDATE camp_temporal SET Validado = 'S', Invalido = NULL WHERE Telefonos = $tel;" );
            };
            return "ok";
        }elseif ($tipo == "invalidos") {
            foreach ($telefonos as $tel){
                $tel = intval($tel);
                $conexion->exec("INSERT or REPLACE INTO telefonos_invalidos (telefono, fecha) VALUES ($tel, '$fecha'); UPDATE camp_temporal SET Invalido = 'S', Validado = NULL WHERE Telefonos = $tel;");
            };
            return "ok";
        } else {
            return "error";
        }
    }

    public static function traer_chequeados($tipo, $session) {
        if ($tipo =="para_chequear"){
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT IDCuenta, Telefonos FROM camp_temporal WHERE Session = $session AND Invalido IS NOT 'S' AND Validado IS NOT 'S';");
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        }else{
            $conexion = new SQLite3('cam.db');
            $result = $conexion->query("SELECT IDCuenta, Telefonos FROM camp_temporal WHERE  Session = $session AND $tipo = 'S';");
            $resultArray = $result->fetchArray(SQLITE3_ASSOC);
        }
        $multiArray = array(); //array to store all rows
        while($resultArray !== false){
            array_push($multiArray, $resultArray); //insert all rows to $multiArray
            $resultArray = $result->fetchArray(SQLITE3_ASSOC); //read next row
        }
        unset($resultArray); //unset temporary variable
        return $multiArray;
    }

    public static function recargar($session){
        $conexion = new SQLite3('cam.db');
        $result = $conexion->query("SELECT COUNT(Telefonos) AS Telefonos, COUNT(Validado) AS Validados, COUNT(Invalido) AS Invalidos FROM camp_temporal WHERE Session ='$session';");
        $resultArray= $result->fetchArray(SQLITE3_ASSOC);
        return $resultArray;
    }
}


?>