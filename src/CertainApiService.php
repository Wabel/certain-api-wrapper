<?php

namespace Wabel\CertainAPI;

/**
 * CertainApiService
 */
class CertainApiService{
    /**
     * An instance of the CertainApiClient
     * @var CertainApiClient
     */
    private $certainClient;

    /**
     * Service constructor
     * @param CertainApiClient $certainClient
     */
    public function __construct(CertainApiClient $certainClient)
    {
        $this->setCertainClient($certainClient);
    }

    public function setCertainClient(CertainApiClient $certainClient)
    {
        $this->certainClient = $certainClient;
        return $this;
    }

    /**
     * Get Account Code
     * @return string
     */
    public function getAccountCode()
    {
        return $this->getCertainClient()->getAccountCode();
    }

    /**
     * Get the certain api client
     * @return CertainApiClient
     */
    public function getCertainClient(){
        return $this->certainClient;
    }

    /**
    * Send a "GET" request to get information about ressource;
    * @param string $ressourceName
    * @param string $ressourcePath
    * @param string $ressourceId
    * @param array $params
    * @param boolean $assoc
    * @param string $contentType
    * @return array
    */
    public function get($ressourceName, $ressourcePath =null, $ressourceId=null, $params = array(),$assoc = false,$contentType='json'){
        return $this->getCertainClient()->get($ressourceName, $ressourcePath, $ressourceId, $params, $assoc,$contentType);
    }

    /**
    * Send a "POST" request to put information to certain;
    * @param string $ressourceName
    * @param string $ressourcePath
    * @param string $ressourceId
    * @param array $bodyData
    * @param array $query
    * @param boolean $assoc
    * @param string $contentType
    * @return array
    */
    public function post($ressourceName, $ressourcePath =null, $ressourceId=null, $bodyData = array(),$query=array(), $assoc = false,$contentType='json'){
        return $this->getCertainClient()->post($ressourceName, $ressourcePath, $ressourceId, $bodyData, $query, $assoc,$contentType);
    }


    /**
    * Send a "PUT" request to put information to certain;
    * @param string $ressourceName
    * @param string $ressourcePath
    * @param string $ressourceId
    * @param array $bodyData
    * @param array $query
    * @param boolean $assoc
    * @param string $contentType
    * @return array
    */
    public function put($ressourceName, $ressourcePath =null, $ressourceId=null, $bodyData = array(),$query=array(), $assoc = false,$contentType='json'){
        return $this->getCertainClient()->put($ressourceName, $ressourcePath, $ressourceId, $bodyData, $query, $assoc,$contentType);
    }
    
    /**
    * Send a "DELETE" request to delete information from certain;
    * @param string $ressourceName
    * @param string $ressourcePath
    * @param string $ressourceId
    * @param boolean $assoc
    * @param string $contentType
    * @return array
    */
    public function delete($ressourceName, $ressourcePath =null, $ressourceId=null, $assoc = false,$contentType='json'){
        return $this->getCertainClient()->delete($ressourceName, $ressourcePath, $ressourceId, $assoc,$contentType);
    }


}