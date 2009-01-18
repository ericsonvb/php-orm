<?php

/*
 *  Esta clase contiene una lista de objetos ORMBase
 *  obtenidos generalmente por medio de getAll.
 *  Sin embargo los objetos no son obtenidos de la base de datos,
 *  sino hasta que realmente son requeridos
 * */

class ORMCollection implements  Iterator, SeekableIterator
{
	
	public static $OFFSET=null;
	
	protected $data=array();
	private $begin = null;
	private $offset = 5;
	
	private $pointer = 0;
	
	public function get($i)
	{	
		$offset = (is_null(self::$OFFSET))?$this->offset:self::$OFFSET;
		if ( $offset < 1 ) $offset = 1;

		if ( $this->begin === null  || $i >= $this->begin+$offset )
		{	
			$conn = $this->obj->getConnection();
			$this->generateSQL();
			$rs = $conn->SelectLimit($this->sql,$offset,$i,$this->params);
			$this->data = array(); 
			foreach($rs as $item)
			{ 
				$this->data[] = $item;
			}
			
			$this->begin = $i;
		}
		
		$obj = clone $this->obj;
		if ( count($this->data) < 2 )
		{
			$obj->setFields($this->data[0]);
		}else
		{
			$obj->setFields($this->data[$i-$this->begin]);
		}
		
		return $obj;
	}
	

	
	public function __construct($obj)
	{
		$this->obj = $obj;
		$this->tablename = $obj->getTableName();
		$this->sql = null;
	}
	
	public function WhereAnd($property,$value=null)
	{
		if ( is_array($property) )
			foreach($property as $key => $value )
				$this->where[" AND ".$key]=$value;	
		else
			$this->where[" AND ".$property]=$value;	
			
		$this->sql = null;
		return $this;
	}
	
	public function WhereOr($property,$value=null)
	{
		if ( is_array($property) )
			foreach($property as $key => $value )
				$this->where[" OR ".$key]=$value;	
		else
			$this->where[" OR ".$property]=$value;	

		$this->sql = null;
		return $this;
	}
	
	public function Orderby($property,$asc=null)
	{
		if ( is_array($property) )
			foreach($property as $key => $value )
				$this->orderby[$key]=$value;	
		else
			$this->orderby[$property] = $asc;

		$this->sql = null;
		return $this;
	}
	
	public function GroupBy($property)
	{
		if ( is_array($property) )
			foreach($property as $key )
				$this->groupby[] = $key;	
		else
			$this->groupby[] = $property;

		$this->sql = null;
		
		return $this;
	}
	
	public function AddSelect($property)
	{
		
	}
	
	private function generateSQL()
	{
		if ( $this->sql != null ) return $this->sql;
	
		$groupby = "";
		if ( isset( $this->groupby ) )
		{
			$groupby = array();
			foreach($this->groupby as $group)
			{	$groupby[] = $group; }
		
			$groupby = " GROUP BY ".implode(",", $groupby);
		}
		
		$orderby = "";
		if ( isset($this->orderby) )
		{
			$orderby = array();
			foreach($this->orderby as $key=>$value)
				$orderby[$key] = ($value==true)?" $key ASC " :" $key DESC ";
			
			$orderby = "ORDER BY ".implode(",",$orderby);
		}
		
		$args = array();
		
		$where = "";
		if ( isset($this->where))
		{
			$where = array();
			foreach($this->where as $key => $value )
			{
				$where[] = $key." ?";
				$args[] = $value;
			}	
			$where = "WHERE 1=1 ".implode(" ",$where);
		}
		
		$sql = "from $this->tablename $where $orderby $groupby";
		
		$this->sql = "SELECT * from $this->tablename $where $orderby $groupby";
		$this->countsql = "SELECT count(*) from $this->tablename $where $orderby $groupby"; 
		$this->params = $args;

		return $sql;
	}
	
	public function rewind() { $this->pointer = 0; }
	public function current() { return $this->get($this->pointer); }
	public function key() { return $this->pointer; }
	public function next() { $this->pointer++; }
	public function valid() { return ($this->pointer < $this->count() ); }
	public function seek($pos) { $this->pointer = ($this->count() > $pos)?$pos:$this->count() - 1; }
	
	public function count() 
	{ 
		if ( ! isset($this->count) )
		{
			$this->generateSQL();
			$conn = $this->obj->getConnection();

		
			$this->count = (int)$conn->GetOne($this->countsql,$this->params);
		}
		return $this->count;
	} 
	
	public function getArray($i)
	{		
		$i = (int) $i;
		if ( $i < 0 ) $i = 0;
		if ( $i > $this->count() ) $i = $this->count();

		$conn = $this->obj->getConnection();
		$this->generateSQL();
		$rs = $conn->SelectLimit($this->sql,$i,$this->pointer,$this->params);
		$response = array();
		foreach($rs as $item)
		{
			$aux = clone $this->obj;
			$aux->setFields($item);
			$response[] = $aux;
		}
		
		return $response;
		
	}
}

?>
