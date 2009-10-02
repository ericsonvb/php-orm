<?php
	/*
		test.php
	*/

	include_once("ORMBase.php");

	class Boleto extends ORMBase
	{
		protected $tablename = "boleto";

		protected function __construct()
		{
		
		}

		public function getConnectionParams()
		{
			return array("server"=>"localhost","database"=>"transportes","user"=>"root","password"=>"","driver"=>"mysql");
		}
	}

	$post = new Post();
	print_r($post);
?>