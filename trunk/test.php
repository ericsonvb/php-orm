<?php

	require('ORM.php');

	class Test extends ORM
	{
		protected $tablename = "notarios";
		
		protected $PK = array("id2");
		protected $names = array("id2"=>"id","login"=>"login","clave"=>"passwd","appaterno"=>"appaterno","apmaterno"=>"apmaterno", "nombre"=>"nombre", "email"=>"email", "telefono"=>"telefono", "celular"=>"celular");
		
		protected function createObject($pk)
		{
			return new Test($pk);
		}
		
		public function __get($attr)
		{
			switch ($attr)
			{
				case "login":
					echo "estamos en login";
					return parent::__get($attr);
					break;
				
				default:
					return parent::__get($attr);
					breaK;
			}
		}
	}

	$a = new Test(null);
	$b = $a->getOneBy("login","czarate");
	echo $b->login;
	echo "\n\n";
?>
