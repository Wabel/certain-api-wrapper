<?php

namespace Wabel\CertainAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Wabel\CertainAPI\Response\CertainResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * CertainApiClient
 * @see http://developer.certain.com/api2docs/introduction
 */
class CertainApiClient
{
    /**
     * URL for call request
     *
     * @var string
     */
    private $baseUri = 'https://appeu.certain.com/certainExternal/service/v1/';

    /**
     *
     * @var Client
     */
    private $client;

    /**
     * AccountCode to put in the URI
     *
     * @var string
     */
    private $accountCode;

    /**
     *
     * @var string
     */
    private $builPath;

    /**
     *
     * @var string
     */
    private $username;

    /**
     *
     * @var string
     */
    private $password;

    /**
     *
     * @param string|null $baseUri
     * @param string $username
     * @param string $password
     * @param string $accountCode
     */
    public function __construct($baseUri, $username, $password,
                                $accountCode)
    {
        if ($baseUri !== null) {
            $this->baseUri = $baseUri;
        }
        $this->username = $username;
        $this->password = $password;
        $this->accountCode = $accountCode;
        $this->setClient(new Client([
            'base_uri' => $this->baseUri
            ]
        ));
    }

    /**
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Define a client
     * @param Client $client
     * @return \Wabel\CertainAPI\CertainApiClient
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get Account Code
     * @return string
     */
    public function getAccountCode()
    {
        return $this->accountCode;
    }

    /**
     * Build the URI to request
     * @param string|array $ressourceName
     * @param string $ressourceId
     * @return string
     */
    private function builPathToCall($ressourceName,$ressourcePath =null, $ressourceId = null)
    {
        $ressourceAdded = '';
        if(!is_null($ressourcePath) && $ressourcePath != ''){
            $ressourceAdded = $ressourcePath;
        }

        if ($ressourceId !== null) {
            $ressourceAdded = '/'.$ressourceId;
        }
        if(!is_null($ressourcePath)){
            $this->builPath = 'accounts/'.$this->getAccountCode().$ressourceAdded;
            return  $this->builPath;
        }else{
            $this->builPath = $ressourceName.'/'.$this->getAccountCode().$ressourceAdded;
            return  $this->builPath;
        }

    }

    /**
     * Make "GET" request with the client.
     * @param string $ressourceName
     * @param string $ressourcePath
     * @param string $ressourceId
     * @param array $query
     * @param boolean $assoc
     * @param string $contentType
     * @return array
     */
    public function get($ressourceName, $ressourcePath =null, $ressourceId = null, $query = array(),
                        $assoc = false, $contentType = 'json')
    {
        try {
            $urlRessource = $this->builPathToCall($ressourceName, $ressourcePath, $ressourceId);
            $response     = $this->getClient()->get($urlRessource,
                array(
                'auth' => [$this->username, $this->password],
                'headers' => ['Accept' => "application/$contentType"],
                'query' => $query,
                'verify' => false
            ));
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        return $this->makeCertainApiResponse($response, $contentType, $assoc);
    }

    /**
     * Make "POST" request with the client.
     * @param string $ressourceName
     * @param string $ressourcePath
     * @param string $ressourceId
     * @param array $bodyData
     * @param array $query
     * @param boolean $assoc
     * @param string $contentType
     * @return array
     */
    public function post($ressourceName, $ressourcePath =null, $ressourceId = null,
                         $bodyData = array(), $query = array(), $assoc = false,
                         $contentType = 'json')
    {
        if ($contentType !== 'json') {
            throw new \Exception('Use only json to update or create');
        }
        try {
            $urlRessource = $this->builPathToCall($ressourceName, $ressourcePath, $ressourceId);
            $response     = $this->getClient()->post($urlRessource,
                array(
                'auth' => [$this->username, $this->password],
                'headers' => ['Accept' => "application/$contentType"],
                'json' => $bodyData,
                'query' => $query,
                'verify' => false
            ));
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        return $this->makeCertainApiResponse($response, $contentType, $assoc);
    }

    /**
     * Make "PUT" request with the client.
     * @param string $ressourceName
     * @param string $ressourcePath
     * @param string $ressourceId
     * @param array $bodyData
     * @param array $query
     * @param boolean $assoc
     * @param string $contentType
     * @return array
     */
    public function put($ressourceName, $ressourcePath =null, $ressourceId = null,
                         $bodyData = array(), $query = array(), $assoc = false,
                         $contentType = 'json')
    {
        if ($contentType !== 'json') {
            throw new \Exception('Use only json to update or create');
        }
        try {
            $urlRessource = $this->builPathToCall($ressourceName, $ressourcePath, $ressourceId);
            $response     = $this->getClient()->put($urlRessource,
                array(
                'auth' => [$this->username, $this->password],
                'headers' => ['Accept' => "application/$contentType"],
                'json' => $bodyData,
                'query' => $query,
                'verify' => false
            ));
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        return $this->makeCertainApiResponse($response, $contentType, $assoc);
    }

    /**
     * Make "DELETE" request with the client.
     * @param string $ressourceName
     * @param string $ressourcePath
     * @param string $ressourceId
     * @param boolean $assoc
     * @param string $contentType
     * @return array
     */
    public function delete($ressourceName, $ressourcePath =null, $ressourceId = null, $assoc = false,
                           $contentType = 'json')
    {
        try {
            $urlRessource = $this->builPathToCall($ressourceName, $ressourcePath, $ressourceId);
            $response     = $this->getClient()->delete($urlRessource,
                array(
                'auth' => [$this->username, $this->password],
                'headers' => ['Accept' => "application/$contentType"],
                'verify' => false
            ));
        } catch (ClientException $ex) {
            $response = $ex->getResponse();
        }
        return $this->makeCertainApiResponse($response, $contentType, $assoc);
    }

    /**
     * Make the  Certain Api repsonse.
     * @param ResponseInterface|null $response
     * @param string $contentType
     * @param boolean $assoc
     * @return array
     */
    private function makeCertainApiResponse($response,
                                            $contentType = 'json', $assoc = false)
    {

        $responseCertainApi = new CertainResponse($response);
        return $responseCertainApi->getResponse($contentType, $assoc);
    }

    public function getBuilPath()
    {
        return $this->builPath;
    }


}