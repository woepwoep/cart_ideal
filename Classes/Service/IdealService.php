<?php
namespace RedSeadog\CartIdeal\Service;

use \RedSeadog\CartIdeal\Service\Configuration\IConnectorConfiguration;
use \RedSeadog\CartIdeal\Service\Log\IConnectorLog;

use \RedSeadog\CartIdeal\Service\Entities\DirectoryRequest;
use \RedSeadog\CartIdeal\Service\Xml\XmlSerializer;
use \RedSeadog\CartIdeal\Service\Xml\XmlSecurity;
use \RedSeadog\CartIdeal\Service\Log\EntityValidator;
use \RedSeadog\CartIdeal\Service\Entities\Merchant;
use \RedSeadog\CartIdeal\Service\Http\WebRequest;

use \RedSeadog\CartIdeal\Service\Exceptions\iDEALException;
use \RedSeadog\CartIdeal\Service\Exceptions\ValidationException;
use \RedSeadog\CartIdeal\Service\Exceptions\SerializationException;
use \RedSeadog\CartIdeal\Service\Exceptions\SecurityException;

use DOMDocument;

use \RedSeadog\CartIdeal\Service\Configuration\DefaultConfiguration;

use \RedSeadog\CartIdeal\Service\Log\DefaultLog;

use \RedSeadog\CartIdeal\Service\Entities\AcquirerStatusRequest;
use \RedSeadog\CartIdeal\Service\Entities\AcquirerTransactionRequest;
use \RedSeadog\CartIdeal\Service\Entities\Transaction;


/**
 *  iDEALConnector Library v2.0
 */
class IdealService implements \TYPO3\CMS\Core\SingletonInterface
{
    private $serializer;
    private $signer;
    private $validator;
    private $configuration;
    private $log;
    private $merchant;

	
    /**
     * @param IConnectorConfiguration $configuration
	 */
	public function setConfiguration(IConnectorConfiguration $configuration)
	{
		$this->configuration = $configuration;
	}

    /**
     * Constructs an instance of IdealService.
     *
     * @param IConnectorConfiguration $configuration An instance of a implementation of IConnectorConfiguration
     * @param IConnectorLog $log An instance of a implementation of IConnectorLog
     */
    public function __construct(IConnectorConfiguration $configuration, IConnectorLog $log)
    {
        $this->log = $log;
        $this->configuration = $configuration;

        $this->serializer = new XmlSerializer();
        $this->signer = new XmlSecurity();
        $this->validator = new EntityValidator();

        $this->merchant = new Merchant($this->configuration->getMerchantID(), $this->configuration->getSubID(), $this->configuration->getMerchantReturnURL());
    }


    /**
     * Get directory listing.
     *
     * @return Entities\DirectoryResponse
     * @throws Exceptions\SerializationException
     * @throws Exceptions\iDEALException
     * @throws Exceptions\ValidationException
     * @throws Exceptions\SecurityException
     */
    public function getIssuers()
    {
        try{
            $request = new DirectoryRequest($this->merchant);

            $this->log->logAPICall("getIssuers()", $request);
            $this->validator->validate($request);

            $response = $this->sendRequest($request, $this->configuration->getAcquirerDirectoryURL());

            $this->validator->validate($response);
            $this->log->logAPIReturn("getIssuers()", $response);

            return $response;
        }
        catch(iDEALException $ex)
        {
            $this->log->logErrorResponse($ex);
            throw $ex;
        }
        catch(ValidationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SerializationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SecurityException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
    }

    /**
     * Start a transaction.
     *
     * @param string $issuerID
     * @param RedSeadog\Cart\Ideal\Entities\Transaction $transaction
     * @param null $merchantReturnUrl
     * @throws RedSeadog\Cart\Ideal\Exceptions\SerializationException
     * @throws RedSeadog\Cart\Ideal\Exceptions\iDEALException
     * @throws RedSeadog\Cart\Ideal\Exceptions\ValidationException
     * @throws RedSeadog\Cart\Ideal\Exceptions\SecurityException
     * @return RedSeadog\Cart\Ideal\Entities\AcquirerTransactionResponse
     */
    public function startTransaction($issuerID, Transaction $transaction,  $merchantReturnUrl = null)
    {
        try{
            $merchant = $this->merchant;

            if (!is_null($merchantReturnUrl))
                $merchant = new Merchant($this->configuration->getMerchantID(), $this->configuration->getSubID(), $merchantReturnUrl);

            $request = new AcquirerTransactionRequest($issuerID, $merchant, $transaction);

            $this->log->logAPICall("startTransaction()", $request);
            $this->validator->validate($request);

            $response = $this->sendRequest($request, $this->configuration->getAcquirerTransactionURL());

            $this->validator->validate($response);
            $this->log->logAPIReturn("startTransaction()", $response);

            return $response;
        }
        catch(iDEALException $iex)
        {
            $this->log->logErrorResponse($iex);
            throw $iex;
        }
        catch(ValidationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SerializationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SecurityException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
    }

    /**
     * Get a transaction status.
     *
     * @param $transactionID
     * @throws Exceptions\SerializationException
     * @throws Exceptions\iDEALException
     * @throws Exceptions\ValidationException
     * @throws Exceptions\SecurityException
     * @return Entities\AcquirerStatusResponse
     */
    public function getTransactionStatus($transactionID)
    {
        try{
            $request = new AcquirerStatusRequest($this->merchant, $transactionID);

            $this->log->logAPICall("startTransaction()", $request);
            $this->validator->validate($request);

            $response = $this->sendRequest($request, $this->configuration->getAcquirerStatusURL());

            $this->validator->validate($response);
            $this->log->logAPIReturn("startTransaction()", $response);

            return $response;
        }
        catch(iDEALException $iex)
        {
            $this->log->logErrorResponse($iex);
            throw $iex;
        }
        catch(ValidationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SerializationException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
        catch(SecurityException $ex)
        {
            $this->log->logException($ex);
            throw $ex;
        }
    }
    
    /*
     * Returns the assigned configuration.
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    private function sendRequest($request, $url)
    {
        $xml = $this->serializer->serialize($request);

        $this->signer->sign(
            $xml,
            $this->configuration->getCertificatePath(),
            $this->configuration->getPrivateKeyPath(),
            $this->configuration->getPassphrase()
        );

        $request = $xml->saveXML();

        $this->log->logRequest($request);

        if(!is_null($this->configuration->getProxy()))
            $response = WebRequest::post($url, $request, $this->configuration->getProxy());
        else
            $response = WebRequest::post($url, $request);

        $this->log->logResponse($response);
            
        if(empty($response))
          throw new SerializationException('Response was empty');

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($response);


        $verified = $this->signer->verify($doc, $this->configuration->getAcquirerCertificatePath());

        if (!$verified)
            throw new SecurityException('Response message signature check fails.');

        return $this->serializer->deserialize($doc);
    }
}

