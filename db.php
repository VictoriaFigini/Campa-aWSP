<?php
class Base{
	const BD_SERVER = "";
	const DB_NAME = "";
	const DB_USERNAME = "";
	const DB_PASSWORD = "";
	const DB_CHARACTERS = "UTF-8";

	private $conexion;
	private $ultima_consulta;

	public function __construct(){
		$this->abrir_conexion();

	}

	public function __destruct(){
		$this->cerrar_conexion();
	}

	public function abrir_conexion(){
		$conectionString = array("Database"=>"", "UID"=>"", "PWD"=>"", 'CharacterSet' =>"UTF-8");
		$this->conexion = sqlsrv_connect("SGIFR", $conectionString);
		if (!$this->conexion) {
			die(print_r(sqlsrv_errors())); //.sqlsrv_errors()
		}
	}

	public function cerrar_conexion(){
		if (isset($this->conexion)) {
			sqlsrv_close($this->conexion);
			unset($this->conexion);
		}
	}

	public function buscar_por_sql($sql){
		$resultado = $this->enviar_consulta($sql);
		return $resultado;
	}

	public function enviar_consulta($sql){
		$this->ultima_consulta = $sql;
		$resultado = sqlsrv_query($this->conexion, $sql);
		$this->verificar_consulta($resultado);
		return $resultado;
	}

	private function verificar_consulta($consulta){
		if (!$consulta) {
			$salida = "No se pudo realizar la consulta. ";
			$salida.= "Última consulta 	SQL: ". $this->ultima_consulta;
			die($salida);
		}
	}
}

$bd = new Base();
?>
