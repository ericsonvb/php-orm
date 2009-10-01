<<<<<<< .mine
<?php
	
class ORMException extends Exception
{
    public $message = 'Unknown exception';
    public $code = ORMException::NoError;
    
    const NoError					= 0;
    const RecordNotFound 		= 1;
    const MissingPrimaryKey 	= 2;
    const PropertyNotValid	 	= 4;
    const InvalidDataType	 	= 8;
    const NullObject				= 16;
    const NotNullObject		 	= 32;
	 const ConnectionError		= 64;
}

class ORMConnectionError extends ORMException{	 
	public $message;
	public $code = ORMException::ConnectionError;
}

class ORMRecordNotFound extends ORMException
{
	public $message;
	public $code = ORMException::RecordNotFound;
}

class ORMMissingPrimaryKey extends ORMException
{
	public $message;
	public $code = ORMException::MissingPrimaryKey;
}

class ORMPropertyNotValid extends ORMException
{
	public $message;
	public $code = ORMException::PropertyNotValid;
}

class ORMInvalidDataType extends ORMException
{
	public $message;
	public $code = ORMException::InvalidDataType;
}

class ORMNullObject extends ORMException
{
	public $message;
	public $code = ORMException::NullObject;
}

class ORMNotNullObject extends ORMException
{
	public $message;
	public $code = ORMException::NotNullObject;
}

?>
=======
<?php
	
class ORMException extends Exception
{
    protected $message = 'Unknown exception';
    protected $code = ORMException::NoError; 
    
    const NoError				= 0;
    const RecordNotFound 		= 1;
    const MissingPrimaryKey 	= 2;
    const PropertyNotValid	 	= 4;
    const InvalidDataType	 	= 8;
    const NullObject		 	= 16;    
    const NotNullObject		 	= 32;
	 const ConnectionError	=64;
}

class ORMConnectionErro extends ORMException{
	 
	 
}

class ORMRecordNotFound extends ORMException
{
	protected $message;
	protected $code = ORMException::RecordNotFound;
}

class ORMMissingPrimaryKey extends ORMException
{
	protected $message;
	protected $code = ORMException::MissingPrimaryKey;
}

class ORMPropertyNotValid extends ORMException
{
	protected $message;
	protected $code = ORMException::PropertyNotValid;
}

class ORMInvalidDataType extends ORMException
{
	protected $message;
	protected $code = ORMException::InvalidDataType;
}

class ORMNullObject extends ORMException
{
	protected $message;
	protected $code = ORMException::NullObject;
}

class ORMNotNullObject extends ORMException
{
	protected $message;
	protected $code = ORMException::NotNullObject;
}

?>
>>>>>>> .r16
