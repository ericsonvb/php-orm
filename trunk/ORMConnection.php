<?php

class ORMConnection
{
	static private $conn = null;

	// Esta Variable contiene la configuracion de nuestra base de datos
	// Debe ser redefinida
	abstract protected $config = 	(
			"server"=>"servidor.com",
			"user"=>"root",
			"pass"=>"clave",
			"database"=>"mibasededatos",
			"driver"=>"mysqlt"
			);

	private function __construct()
	{
		include_once("adodb.inc.php");
		$conn = ADONewConnection();
		$conn->PConnect("serverip", "user", "pass", "database");
		$this->conn = $conn;
		$this->conn->debug=false;
		$this->conn->SetFetchMode(ADODB_FETCH_ASSOC);

	}

	static function getConnection()
	{
		if ( self::$conn == null or self::$conn)
		{
			self::$conn = ADONewConnection(self::$config["driver"]);
			self::$conn->PConnect(self::$config["server"],self::$config["user"],self::$config["pass"],self::$config["database"]);
			self::$conn->debug = false;
			self::$conn->SetFetchMode(ADODB_FETCH_ASSOC);
		}
		return self::$conn;
	}
}


?>
