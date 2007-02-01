<?php

class DB
{
	var $conn = null;

	function __construct()
	{
		include_once("adodb.inc.php");
		$conn = ADONewConnection('mysqlt');
		$conn->PConnect("perunotarios.com", "root", "kakarotto", "redprivada");
		$this->conn = $conn;
		$this->conn->debug=false;
		$this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
	
	}

	function getConnection()
	{
		return $this->conn;
	}
}


?>
