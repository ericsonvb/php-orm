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
