<?php

	require_once("DB.php");

abstract class ORM
{
	protected $tablename = "";

	// Es un array multidimensional que contiene la informacion sobre
	// la estructura de la base de datos, asi como los valores del
	// Registro Actual.
	abstract protected $vars;
	// $vars[nombreVariable] =>   [ columnName, value, pk, fk ]

	protected $bModified = false;
	private $bNull = false;

}

	/*abstract class ORM
	{
		const TYPECOLUMN_NM = 1;
		const TYPECOLUMN_PK = 2;
		const TYPECOLUMN_FK = 4;

		// Contiene las variables del objeto
		// nombrevariable = Valor Variable
		protected $vars = array();

		// Define los nombres de las variables y su correspondiente nombre de columna
		// nombre variable = nombre columna
		protected $names = array();

		// Define las variables que conforman la PK
		//nombre variable = none
		protected $PK = array();

		// Define las variables que conforman las FK
		// nombre variable = nombre Clase
		protected $FK = array();

		// Define que el objeto a sido modificado y debe ser persitido
		protected $bModified = false;

		// Define si el objeto esta linkeado con un registro de la DB
		// Si fuera true, el objeto no tendria permisos de escritura.
		private $bNull = false;


		// EL constructor consulta a la base de datos por el registro
		// que contiene los PK's $pk. Si no existe devuelve una excepcion
		//
		// $pk es un array asociativo, que contiene los nombres de variables
		// y el valor para cada variable que forma parte del PK
		//
		// Si $pk fuera null, se construira el objeto sin hacer referencia a ningun registro de la Base de datos.
		// Por lo tanto tendra desactivadas el acceso a los attributos y a las acctualizaciones.
		// Este tipo de Objetos servira para poder utilizar lo que antaño fuera las funciones estaticas
		public function __construct($pk=null)
		{
			if ( is_null($pk) )
			{
				$this->bNull = true;
				return null;
			}

			// Verificamos que la informacion enviada sea sintacticamente correcta.
			// El motivo, es evitar gastar recursos en crear una conexion o enviar la
			// misma si los datos no son conformes.

			//Verificamos que el $pk contenga informacion
			if ( sizeof($pk) < 1 )
			{ 	//Lanzar Excepcion indicando que no se ha enviado informacion al constructor
				throw new Exception ("No se ha enviado información al constructor");
			}

			if ( ! ( sizeof($pk)  == sizeof($this->PK) ) )
			{	//Lanzar Excepcion indicando que el constructor no tiene el numero exacto de parametros
				throw new Exception ("Faltan parametros ...");
			}

			//Verificamos que los nombres de los indices del $pk, correspondan a los
			//declarados en la clase final
			foreach ( $pk as $key => $value )
			{
				if ( ! $this->existsColumnName($key,$TYPECOLUMN_PK) )
				{	//Lanzar Excepcion indicando que los nombres no coinciden
					throw new Exception ("El nombre $key no esta relacionado a ningun campo de la tabla $tablename");
				}
			}

			$sql = "select * from $this->tablename where 1=1 ";

			foreach ( $pk as $key => $value )
			{
				$columnName = $this->names[$key];
				$sql_aux = " and $columnName='$value' ";
				$sql = $sql . $sql_aux;
			}

			// Se crea la conexion con la base de datos
			$database = new DB();
			$con = $database->getConnection();
			$rs = $con->getRow($sql);
			$con->Close();

			if ( ($rs !== false) and ( sizeof($rs) > 0 ) )
			{
				foreach( $this->names as $key => $value )
				{
					$this->vars[$key] = $rs[$value];
				}
			}
			else
			{
				throw new Exception ("No existe el registro especificado");
			}

		}

		// Verifica si el nombre de variable existe en los diferentes
		// diccionarios ($vars,$pk,$fk)
		// El parametro $type, define donde tendra q hacer esa validacion.
		private function existsColumnName($name,$type=TYPECOLUMN_NM)
		{
			return true;
		}


		public function toString()
		{
			echo "\n";
			print_r($this->vars);
			print_r($this->names);
		}


		// Me permite obtener el valor de las variables
		public function __get($attr)
		{
			if ( ! isset($this->names[$attr]) )
			{
				throw new Exception("No existe el attributo especificado");
			}

			$response = $this->vars[$attr];

			if ( isset($this->FK[$attr]) )
			{
				// El Attributo es una variable foranea por tanto debe ser instanciado primero

				// Aca probablemente deba pensarse el uso de una factoria.

			}

			return $response;
		}

		public function __set($attr,$value)
		{
			if ( ! isset($this->names[$attr]) )
			{
				throw new Exception("No existe el attributo especificado");
			}

			$this->vars[$attr] = $value;

			$this->bModified = true;
		}


		// Esta funcion permite obtener un objeto usando un campo identificativo
		// $varname define el nombre de la variable del objeto
		// $value define el valor a buscar en dicho campo
		//
		// Si $varname no corresponde a un campo UNIQUE dentro de la tabla,
		// la funcion solo devolvera un objeto, lo cual probablemente no sea el
		// comportamiento deseado. Para obtener un array de objetos usar getBy
		public function getOneBy($varname,$value)
		{
			$columnName = $this->names[$varname];

			$pk = "";
			foreach($this->PK as $key)
			{
				$pkname = $this->names[$key];
				$pk = $pk . " $pkname,";
			}
			$pk = substr($pk,0,strlen($pk)-1);

			$sql = "select $pk from $this->tablename where $varname='$value' ";

			$database = new DB();
			$con = $database->getConnection();
			$rs = $con->getRow($sql);
			$con->Close();

			$pk = array();

			if ( ($rs !== false) and ( sizeof($rs) > 0 ) )
			{
				foreach( $rs as $key=>$value)
				{
					$pk[$key]=$value;
				}
			}

			return $this->createObject($pk);

		}

		protected abstract function createObject($pk);

		// Permite persistir el objeto
		public function update()
		{
			$sql = "update $this->tablename set ";

			foreach( $this->vars as $key => $value )
			{
				$columnName = $this->names[$key];
				$sql_aux = "$columnName='$value' ,";
				$sql = $sql . $sql_aux;
			}

			$sql = substr($sql,0,strlen($sql)-1);
			$sql = $sql. " where 1=1 ";

			foreach( $this->PK as $key)
			{
				$columnName = $this->names[$key];
				$value = $this->vars[$key];
				$sql_aux = " and $columnName='$value' ";
				$sql = $sql . $sql_aux;
			}


			$database = new DB();
			$con = $database->getConnection();
			$con->Execute($sql);
			$con->Close();
		}

	}

*/


?>