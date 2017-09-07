<?php

namespace Wabel\CertainAPI;

use Wabel\CertainAPI\Interfaces\CertainRessourceInterface;
use Wabel\CertainAPI\Interfaces\CertainResponseInterface;
/**
 * CertainRessourceAbstracct for common action about Ressource
 *
 * @author rbergina
 */
use Wabel\CertainAPI\CertainApiService;

abstract class CertainRessourceAbstract implements CertainRessourceInterface, CertainResponseInterface
{
    const NOT_FOUND = 404;

    /**
     * CertainApiService
     * @var CertainApiService
     */
    protected $certainApiService;

    /**
     * array of results with information about the request
     * @var array
     */
    protected $results;

    /**
     *
     * @var string
     */
    protected $ressourceCalled;

    /**
     * @param CertainApiService $certainApiService
     */
    public function __construct(CertainApiService $certainApiService)
    {
        $this->certainApiService = $certainApiService;
    }

    /**
     * Get information about ressource
     * @param string $ressourceId
     * @param array $params
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     * @throws Exceptions\RessourceException
     */
    public function get($ressourceId = null, $params = array(), $assoc = false,
                        $contentType = 'json')
    {
        $ressourceName = $this->getRessourceName();
        ;
        if ($ressourceName == '' || is_null($ressourceName)) {
            throw new Exceptions\RessourceException('No ressource name provided.');
        }
        $this->results = $this->certainApiService->get($ressourceName,
            $this->ressourceCalled, $ressourceId, $params, $assoc, $contentType);
        return $this;
    }

    /**
     * Add/Update information to certain
     * @param array $bodyData
     * @param array $query
     * @param string $ressourceId
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     * @throws Exceptions\RessourceException
     * @throws Exceptions\RessourceMandatoryFieldException
     */
    public function post($bodyData, $query = array(), $ressourceId = null,
                         $assoc = false, $contentType = 'json')
    {
        $ressourceName = $this->getRessourceName();
        ;
        if ($ressourceName == '' || is_null($ressourceName)) {
            throw new Exceptions\RessourceException('No ressource name provided.');
        }
        if ($ressourceId === null && count($this->getMandatoryFields()) > 0) {
            foreach ($this->getMandatoryFields() as $field) {
                if (!in_array($field, array_keys($bodyData))) {
                    throw new Exceptions\RessourceMandatoryFieldException(sprintf('The field %s is required',
                        $field));
                }
            }
        }
        $this->results = $this->certainApiService->post($ressourceName,
            $this->ressourceCalled, $ressourceId, $bodyData, $query, $assoc,
            $contentType);
        return $this;
    }

    /**
     * Update information to certain
     * @param array $bodyData
     * @param array $query
     * @param string $ressourceId
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     * @throws Exceptions\RessourceException
     * @throws Exceptions\RessourceMandatoryFieldException
     */
    public function put($bodyData, $query = array(), $ressourceId = null,
                        $assoc = false, $contentType = 'json')
    {
        $ressourceName = $this->getRessourceName();
        ;
        if ($ressourceName == '' || is_null($ressourceName)) {
            throw new Exceptions\RessourceException('No ressource name provided.');
        }
        if (!is_null($ressourceId) && count($this->getMandatoryFields()) > 0) {
            foreach ($this->getMandatoryFields() as $field) {
                if (!in_array($field, array_keys($bodyData))) {
                    throw new Exceptions\RessourceMandatoryFieldException(sprintf('The field %s is required',
                        $field));
                }
            }
        } else {
            throw new Exceptions\RessourceMandatoryFieldException(sprintf('The id field is required'));
        }
        $this->results = $this->certainApiService->put($ressourceName,
            $this->ressourceCalled, $ressourceId, $bodyData, $query, $assoc,
            $contentType);
        return $this;
    }

    /**
     * Delete information from certain
     * @param string $ressourceId
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     * @throws Exceptions\RessourceException
     */
    public function delete($ressourceId, $assoc = false, $contentType = 'json')
    {
        $ressourceName = $this->getRessourceName();
        if ($ressourceName == '' || is_null($ressourceName)) {
            throw new Exceptions\RessourceException('No ressource name provided.');
        }
        $this->results = $this->certainApiService->delete($ressourceName,
            $this->ressourceCalled, $ressourceId, $assoc, $contentType);
        return $this;
    }

    /**
     * Check is a successful request
     * @return boolean
     */
    public function isSuccessFul()
    {
        return $this->results['success'];
    }

    /**
     * Check is not found.
     * @return boolean
     */
    public function isNotFound()
    {
        if (isset($this->results['statusCode']) && $this->results['statusCode'] == self::NOT_FOUND) {
            return true;
        } elseif (isset($this->results['statusCode']) && $this->results['statusCode']
            != self::NOT_FOUND) {
            return false;
        }
        return null;
    }

    /**
     * Get the results
     * @return \stdClass|\stdClass[]|array
     */
    public function getResults()
    {
        return $this->results['results'];
    }

    /**
     * Get the succes value, results,  success or fail reason
     * @return array
     */
    public function getCompleteResults()
    {
        return $this->results;
    }

    public function getRessourceCalled()
    {
        return $this->ressourceCalled;
    }

    public function createRessourceCalled($ressourceCalledParameters = null)
    {
        $this->ressourceCalled = '';
        if (is_array($ressourceCalledParameters) && !empty($ressourceCalledParameters)) {
            foreach ($ressourceCalledParameters as $segmentKey => $segmentValue) {
                if ($segmentValue != '') {
                    $this->ressourceCalled .= '/'.$segmentKey.'/'.$segmentValue;
                } else {
                    $this->ressourceCalled .= '/'.$segmentKey;
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param string $eventCode
     * @param string $ressourceId
     * @param array $params
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     */
    public function getWithEventCode($eventCode, $ressourceId, $params = array(),
                                     $assoc = false, $contentType = 'json')
    {
        $ressourceId = $eventCode.'/'.$ressourceId;
        return $this->get($ressourceId, $params, $assoc, $contentType);
    }

    /**
     *
     * @param string $eventCode
     * @param string $ressourceId
     * @param array $bodyData
     * @param array $query
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     */
    public function postWithEventCode($eventCode, $ressourceId, $bodyData,
                                      $query = array(), $assoc = false,
                                      $contentType = 'json')
    {
        $ressourceId = $eventCode.'/'.$ressourceId;
        return $this->post($bodyData, $query, $ressourceId, $assoc, $contentType);
    }

    /**
     *
     * @param string $eventCode
     * @param string $ressourceId
     * @param array $bodyData
     * @param array $query
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     */
    public function putWithEventCode($eventCode, $ressourceId, $bodyData,
                                     $query = array(), $assoc = false,
                                     $contentType = 'json')
    {
        $ressourceId = $eventCode.'/'.$ressourceId;
        return $this->put($bodyData, $query, $ressourceId, $assoc, $contentType);
    }

    /**
     *
     * @param type $eventCode
     * @param string $ressourceId
     * @param boolean $assoc
     * @param string $contentType
     * @return \Wabel\CertainAPI\CertainRessourceAbstract
     */
    public function deleteWithEventCode($eventCode, $ressourceId,
                                        $assoc = false, $contentType = 'json')
    {
        $ressourceId = $eventCode.'/'.$ressourceId;
        return $this->delete($ressourceId, $assoc, $contentType);
    }

    /**
     * Return the size.
     * @return int
     */
    public function getSize()
    {
        $result = $this->getResults();
        if (!isset($result->size)) {
            return null;
        }
        return $result->size;
    }

    /**
     * Return the completeCollectionSizet.
     * @return int
     */
    public function getCompleteCollectionSize()
    {
        $result = $this->getResults();
        if (!isset($result->size)) {
            return null;
        }
        return $result->completeCollectionSize;
    }

    /**
     * Return the maxResults.
     * @return int
     */
    public function getMaxResults()
    {
        $result = $this->getResults();
        if (!isset($result->size)) {
            return null;
        }
        return $result->maxResults;
    }

    /**
     *
     * @return CertainApiService
     */
    public function getCertainApiService()
    {
        return $this->certainApiService;
    }


}