<?php

class DB
{
	var $conn = null;

	function __construct()
	{
		include_once("adodb.inc.php");
		$conn = ADONewConnection('mysqlt');
		$conn->PConnect("serverip", "user", "pass", "database");
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
