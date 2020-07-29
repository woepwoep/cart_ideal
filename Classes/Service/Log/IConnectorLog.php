<?php
namespace RedSeadog\SfEventMgtIdeal\Service\Log;

use \RedSeadog\SfEventMgtIdeal\Service\Entities\AbstractResponse;
use \RedSeadog\SfEventMgtIdeal\Service\Exceptions\ConnectorException;
use \RedSeadog\SfEventMgtIdeal\Service\Exceptions\iDEALException;
use \RedSeadog\SfEventMgtIdeal\Service\Entities\AbstractRequest;

/**
 *  Implement this interface to get access to log messages at transport level.
 */
interface IConnectorLog
{

    /**
     * @param iDEALException $exception
     * @return
     */
    public function logErrorResponse(iDEALException $exception);

    /**
     * @param ConnectorException $exception
     */
    public function logException(ConnectorException $exception);

    /**
     * @param string $method
     * @param AbstractRequest $request
     * @return
     */
    public function logAPICall($method, AbstractRequest $request);

    /**
     * @param string $method
     * @param AbstractResponse $response
     * @return
     */
    public function logAPIReturn($method, AbstractResponse $response);

    /**
     * @param string $xml
     * @return
     */
    public function logRequest($xml);

    /**
     * @param string $xml
     * @return
     */
    public function logResponse($xml);
}
