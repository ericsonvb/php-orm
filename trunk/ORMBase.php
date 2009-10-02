<?php
/*
 *      ORMBase.php
 *      
 *      Copyright 2008 necudeco <necudeco@arthas>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software;

 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

// Version 	2.1.4
// Fecha:	15/03/2009
// Author:	necudeco@necudeco.com


require_once('adodb/adodb.inc.php');
include_once('adodb/adodb-exceptions.inc.php');
//include_once('adodb/toexport.inc.php');



include_once("ORMMetaData.php");
include_once("ORMCollection.php");
include_once("ORMException.php");

global $phpORM_debug;


/**
* ORMBase
* Clase Abstracta que implementa el acceso a los campos de un
* registro de una tabla en particular
*/
abstract class ORMBase implements Iterator 
{
	/**
	* Nombre de la tabla que sera controlada por esta clase.
	*/
	protected $tablename;
	
	/**
	* Campos obtenidos por defecto de la base de datos
	* Caracteristica aun no implementada.
	* NO MODIFICAR
	*/
	protected $selectFields = "*";
	
	
	protected $fields;
	protected $primarykeys;	
	protected $metainfo;
	
	protected $className;
	
	protected $bModified;
	protected $bNull;

    protected $sql = null;

	protected $hasone=array();
	protected $hasmany=array();
	
	// Definicion de propiedades no existentes en la base de datos
	// key => definicion de la propiedad 
	protected $virtual = array();
	
	// Se encarga de almacenar temporalmente los resultados de las peticiones hasone y hasmany
	private $cache = array();
	
	// me permite definir nombres de campos equivalentes en las tablas
	// Solo se usa en caso de ser necesario cuando tenemos hasone o hasmany
	protected $translator = null;
	
	private $__friends = array("ORMCollection","ORMBase");
	 
	protected $autoreplaceNull = true;

    protected $exceptions = false;
	
	public function rewind() { reset($this->fields); }
	public function current() { return current($this->fields); }
	public function key() { return key($this->fields); }
	public function next() { return next($this->fields); }
	public function valid() { return ($this->current() !== false ); }
	
	

	/**
	* Devuelve el nombre de la tabla que maneja este objeto.
	*/
	public function getTableName() {return $this->tablename; }


	/*
		Esta funcion se encarga de obtener informacion acerca de la 
		estructura de la tabla. Es llamada automaticamente por el 
		constructor
	*/
/*
 * 
 * name: _getMetadata
 * @param
 * @return
 */
	public function _getMetadata()
	{	
		if ( $this->metainfo !== null ) 
		{
			$response['fields'] = $this->fields;
			$response['primarykeys'] = $this->primarykeys;
			$response['metainfo'] = $this->metainfo;
		}else
		{
			$conn = $this->getConnection(); 
			$rs = $conn->MetaColumns($this->tablename,False);
	
			foreach($rs as $item)
			{	
				$fields[$item->name]=null;
				$metainfo[$item->name]= (array)$item; //array("type"=>$item->type,"length"=>$item->max_length);
				if ( $item->primary_key === true )
				{
					$primarykeys[$item->name] = ($item->auto_increment===true)?-1:0;
				}
			}
			//$metainfo =  $rs;
			$response["fields"] = &$fields;
			$response["primarykeys"] = &$primarykeys;
			$response["metainfo"] = &$metainfo;
		}
		
		return $response;
	}
	 
    public function getSQL()
    {
//        $this->Execute($this->sql['sql'], $this->sql['values']);
    }

/*
 * 
 * name: __construct
 * @param
 		$args:  * lista columnas PK y sus respectivos valores
 				* null
 * @return:		* El objeto relacionado con un registro de la base de datos
 				* El objeto sin relacion con ningun registro de la base de datos
 */
	 

	/**
	*	Crea un objeto tipo ORMBase, obteniendo un registro de la base de datos
	*   cuyas claves PK coincidan con el argumento $args
	*   Internamente llama a $this->find
	*/
	public function __construct($args=null)
	{		
		$this->className = get_class($this);
		
		$metadata = ORMMetadata::getMetadata($this);
		$this->fields = $metadata["fields"];
		$this->primarykeys =  $metadata["primarykeys"];
		$this->metainfo = $metadata["metainfo"];

		if ( is_null($args) )
		 {	
			$this->bNull = True;
			return ;
		 }
		  
		$this->find($args);
	}

  public function type($property)
  {
	if ( ! isset($this->metainfo[$property])) return null;
	return $this->metainfo[$property]["type"];
  }

  public function length($property)
  {
	if ( ! isset($this->metainfo[$property])) return null;
	return $this->metainfo[$property]["length"];
  }

/*
 * 
 * name: find
 * @param :		* lista columnas PK y sus respectivos valores
 * @return		* El objeto relacionado con un registro de la base de datos
 */
	public function find($args)
	{
		$virtual = "";
		foreach ($this->virtual as $k => $v)
		{
			$virtual .= " , ( $v ) as $k  ";
		}
	
		$sql = "select *, $virtual from $this->tablename where ";
		$where = "";
		$whereargs = array();
		foreach ($this->primarykeys as $key=>$value)
		{
			if ( ! isset($args[$key]) )	
				 throw new ORMMissingPrimaryKey("Falta argumento $key en la clave primaria ($this->className)");
			 
			$whereargs["$key=?"] = $args[$key];
		 }

		$where = join(" and ",array_keys($whereargs));

		 $sql = "$sql $where";
		 $conn = $this->getConnection();
		  
		 $rs = $conn->GetRow($sql,$whereargs); 
		 if ( sizeof($rs) === 0 ) throw new ORMRecordNotFound("No existe el registro ($this->className)");
		 
		 $this->setFields($rs);
		 		 
		 $this->bModified = false;
		 $this->bNull = false;

	}


	static public function debug($debug=false)
	{
		global $phpORM_debug;
		$phpORM_debug = ($debug === false)?false:true;		
	}

	
	public function getPK()
	{
		return $this->primarykeys;
	}

	public function getColumns()
	{
		return array_keys($this->fields);
	}

	protected function translate($class,$key)
	{	
		if ( $this->translator === null ) return $key; 
		if ( isset($this->translator[$class]))
		{ 
			if (isset($this->translator[$class][$key] ))
			{	
				return $this->translator[$class][$key];
			}
		}
		
		return $key;
	}

	private function getField($attr)
	{
		if ( method_exists($this, "_get_$attr") )
		{
			$method = "_get_$attr";
			return $this->$method();
		}else{
			return $this->fields[$attr];
		}		
	}
		
	
	private function getOne($attr)
	{	
		$className = $this->hasone[$attr];

		$filename = strtolower($className);
		if ( ! class_exists($className,false) )
			include_once("models/$filename.php");

		$aux = new $className();
		$pk = $aux->getPK(); 
		if ( $this->translator === null )
			$pk = $this->fields;
		else
		{
			foreach ($pk as $key => $value )
			{
				//$keyname = $this->translate($className,$key);
				$keyname = $this->translate($attr,$key);
				$pk[$key]=$this->fields[$keyname]; 
			}
		}

        try{
            $aux->find($pk);
        }catch(Exception $e)
        {
            if ( $this->exceptions == true) throw $e;
            return null;
        }
		return $aux;
	
	}

	private function getMany($attr)
	{	
		$className = $this->hasmany[$attr];

		$filename = strtolower($className);
		if ( ! class_exists($className,false) )
			include_once("models/$filename.php");
		
	

		$aux = new $className();
		$pk = $this->getPK();
		$pk2 = array();
		$response =	$aux->getAll();
		foreach ($pk as $key => $value )
		{
			$key2 = $this->translate($className,$key);
			$pk2["$key2 ="]=$this->fields[$key];
			$response = $response->WhereAnd("$key2 = ",$this->fields[$key]);
		}
		
		return $response;		
	}

	public function __get($attr)
	{	

		if ( array_key_exists($attr,$this->fields)) // Buscamos la propiedad entre las columnas de la tabla
		{
			return $this->getField($attr);
		}
		elseif ( array_key_exists($attr,$this->hasone) ) // Se debe devolver un objeto no un valor
		{
			if ( ! isset($cache["one"][$attr]) )
				$cache["one"][$attr] = $this->getOne($attr);
			return $cache["one"][$attr];
		}
		elseif (array_key_exists($attr,$this->hasmany))		//
		{
			if ( ! isset($cache["many"][$attr]) )
				$cache["many"][$attr] = $this->getMany($attr);
			return $cache["many"][$attr];
		}
		else
			throw new ORMPropertyNotValid("Get: Propiedad $attr No valida");
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setFields($fields = array() )
	{	
		if ( ! is_array($fields) )
		{
			$this->clean();
			return;
		}
		
		foreach($this->fields as $key=>$value)
		{
			if ( array_key_exists($key,$fields) )
				$this->fields[$key] = $fields[$key];
		}
		$this->bNull = false;
		$this->bModified = true;
	}
	 
	public function __set($attr,$value)
	{
		if ( array_key_exists($attr,$this->fields))
		{
			// ponemos el valor en el formato especificado por la base de datos
			
			if ( array_key_exists($attr, $this->metainfo))
			{
				$formatfunction = "__format".$this->metainfo[$attr]["type"];
				if ( method_exists($this, $formatfunction) ) 
					$value = $this->$formatfuncion($value,$this->metainfo[$attr]);
			}
			
			if ( method_exists($this,"_set_$attr"))
			{
				$method = "_set_$attr";
				$this->$method($value);
			}else
			{
				$this->fields[$attr] = $value;
				
			}
			$this->bModified = true;
		}
		else
			throw new ORMPropertyNotValid("Set: Propiedad $attr No Valida");
	}
	
	//TODO: getConnection migrada v1
	public function getConnection()
	{
		/*
		* Codigo anexado para permitir la implementacion de funciones amigas
		**/
		$trace = debug_backtrace();
		$trace = $trace[0];

		// La llamada provino de una clase amiga ?
		if(isset($trace['class']) && in_array($trace['class'],$this->__friends)) 
		{
			/*
			if ( isset($GLOBALS["conn"][get_class($this)]) )
			{
				$conn = $GLOBALS["conn"][get_class($this)];
				return $conn;
			}*/
			$config_db = $this->getConnectionParamaters();

			$conn = ADONewConnection($config_db["driver"]); 
			$conn->Connect($config_db["server"],$config_db["user"],$config_db["password"],$config_db["database"]);
			if ( $conn == null)
			{
				throw new ORMConnectionError("Error de Conexion");
			}
			if ( ! isset($config_db["charset"]) ) $config_db["charset"] = "utf8";
			$conn->execute("SET NAMES ".$config_db["charset"]);
			$GLOBALS["phpORM"]["filedebug"]= isset($config_db["filedebug"])?$config_db["filedebug"]:null;
			global $phpORM_debug;
			$conn->debug = $phpORM_debug;
			$conn->SetFetchMode(ADODB_FETCH_ASSOC);
            //$conn->LogSQL();
			//global $conn["className"]

			//$GLOBALS["conn"][get_class($this)] = $conn;

			return $conn;            
        }else{
        	trigger_error("La funcion getConnection no es una funcion publica");
		}
	}
	
	protected function getConnectionParamaters()
	{
		
		include("config_db.php");
		return $config_db;
	}

	public function update()
	{
		if ( $this->bNull ) throw new ORMNullObject("No se puede actualizar un objeto inexistente");
		 
		if ( $this->bModified )
		{	
			
			$sql = "update $this->tablename set ";
			$set = "";
			$setargs = array();
			$where = ""; 
			$whereargs = array(); 
			foreach($this->fields as $key => $value )
			{ 
				if ( ! array_key_exists($key,$this->primarykeys) )
				{ 
					$set = "$set $key=?,";
					if ( $this->metainfo[$key]["type"] == "varchar" or $this->metainfo[$key]["type"] == "int" ) 
						$value = substr($value,0,$this->metainfo[$key]["max_length"]);
					if ( is_null($value) ) 
					{
						if ( isset($this->metainfo[$key]['has_default'])) 
							$value = $this->metainfo[$key]['has_default'];
						elseif ( $this->metainfo[$key]['not_null'] == 0 )
							$value = null;
						else
						{
							switch ( $this->metainfo[$key]['type'] )
							{
								case 'varchar':
									$value = '';
									break;
								case 'int':
									$value = 0;
									break;
								default:
									$value = '';
									break;
							}
						}
					}
					$setargs[] = $value;
				}else{
					$where = "$where $key=? and";
					$whereargs[]= $value;	
				}
			}
	
			$where = substr($where,0,strlen($where)-3);
			$set   = substr($set  ,0,strlen($set)-1);
			$sql = "$sql $set where $where";
	
			$args = array_merge($setargs,$whereargs);
			$conn = $this->getConnection(); 
			$conn->Execute($sql,$args);
			$this->bModified = false;
		}
	}
	
	// Devuelve true en caso que el objeto actual este registrado en la base de datos
	public function exists() { return ! $this->bNull; } 


	// registra al objeto actual en la base de datos
	public function create($force=false)
	{	
	  if ( $force == false )
		if ( ! $this->bNull ) throw new ORMNotNullObject("No se puede crear un objeto ya existente");
		
		$className = get_class($this);
		
		$autoinc = null;
		foreach( $this->primarykeys as $key => $value )
		{
			if ( $value === -1 ) // es autonimerico
			{
			  $autoinc = $key;
			  continue;
			 }
			if ( is_null($this->fields[$key])) 
			{
				throw new ORMMissingPrimaryKey("Create: Falta clave primara $key");
			}
		}

		$keys = "";
		$values = "";
		$valuesargs = array(); 
		foreach ($this->fields as $key=>$value)
		{
			if ( $value === null ) continue;
			if ( $key === $autoinc ) continue;
			$keys = "$keys $key,";
			$values = "$values ?,"; 
			
			if ( $this->metainfo[$key]["type"] == "varchar" or $this->metainfo[$key]["type"] == "int" ) $value = substr($value,0,$this->metainfo[$key]["max_length"]);
			$valuesargs[] = ($this->autoreplaceNull and is_null($value))?" ":$value;
		}
			
		$keys = substr($keys,0,strlen($keys)-1);
		$values = substr($values,0,strlen($values)-1);
		
		$sql = "insert into $this->tablename ($keys) values ( $values ) "; 
		$conn = $this->getConnection();

        $this->sql['sql'] =  $sql;
        $this->sql['values'] = $valuesargs;
    
		$rs = $conn->Execute($sql,$valuesargs); // Donde se llama a addslahes
		
		$this->bNull = false;
		$this->bModified = false;
		if ( is_null($autoinc) ) // NO se genero autoincremento
		{
			
		}else{
			$this->fields[$autoinc]=$conn->Insert_ID();
		}		
	}
	 
	public function clean()
	{
		$this->_getMetadata();
		$this->bNull = true;
	}
	 
	

	public function getAll()
	{
		$coll = new ORMCollection($this);

		return $coll;
	}

  	// Se encarga de borrar los objetos de la base de datos
  	public function delete()
  	{
  		if ( $this->bNull === false )
  		{
  			
  			$where = "";
  			$whereargs = array();
  			foreach ($this->primarykeys as $key=>$value)
  			{
  				$where = "$where $key=? and";
  				$whereargs[] = $this->fields[$key];
  			}
  			$where = substr($where,0,strlen($where)-3);
  			$sql = "delete from $this->tablename where $where";

  			$conn = $this->getConnection();
  			$conn->Execute($sql,$whereargs);
  		}
  	}

	public function toJSON()
	{
		return json_encode($this->fields);
	}

    public function toArray()
    {
        return $this->fields;
    }

    public function Execute($sql,$params=null)
    {
        $conn = $this->getConnection();

		if ( is_null($params ) ) $params = array();
		
        $rs = $conn->Execute($sql,$params);
        $aux = array();
        foreach ( $rs as $item ) $aux[] = $item;
        return $aux;
    }
  	

}


?>