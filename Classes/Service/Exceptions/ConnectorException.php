<?php
namespace RedSeadog\SfEventMgtIdeal\Service\Exceptions;

use Exception;

class ConnectorException extends Exception
{

    function __construct($message)
	{
		debug($message,'ValidationException: message');exit(1);
	}
}
