<?php

namespace Wabel\CertainAPI\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * CertainResponse to clean client Response
 *
 * @author rbergina
 */
class CertainResponse
{

    /**
     *
     * @var ResponseInterface|null
     */
    private $response;

    /**
     *
     * @param ResponseInterface|null $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Decode a string to json. If a jsonp we convert ton json string before deconding.
     * @param string $jsonp
     * @param boolean $assoc
     * @return \stdClass|array
     */
    public function jsonp_decode($jsonp, $assoc = false)
    {
        // Test we have a JSONP
        if ($jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp, '();'), $assoc);
    }

    /**
     * Response about results
     * @param string $contentType
     * @param boolean $assoc
     * @return array
     */
    public function getResponse($contentType='json',$assoc = false)
    {
        $response = array(
            'statusCode' => $this->response?$this->response->getStatusCode():null,
            'success' => false,
            'results' => null,
            'message' => $this->response?$this->response->getReasonPhrase():null
        );
        if ($this->response && in_array($this->response->getStatusCode(), array(200, 201))) {
            $streamBody = $this->response->getBody();
            $bodyString = $streamBody->getContents();

            if($contentType === 'json'){
               $response['results'] = $this->jsonp_decode($bodyString, $assoc);
            }else{
               $response['results']=$bodyString;
            }

            $response['success'] = true;
        }
        return $response;
    }
}