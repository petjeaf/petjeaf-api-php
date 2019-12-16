<?php

namespace Petjeaf\Api\Endpoints;

use Petjeaf\Api\Exceptions\ApiException;
use Petjeaf\Api\PetjeafApiClient;

abstract class EndpointAbstract
{
    const REST_CREATE = PetjeafApiClient::HTTP_POST;
    const REST_UPDATE = PetjeafApiClient::HTTP_PATCH;
    const REST_READ = PetjeafApiClient::HTTP_GET;
    const REST_LIST = PetjeafApiClient::HTTP_GET;
    const REST_DELETE = PetjeafApiClient::HTTP_DELETE;

    /**
     * @var PetjeafApiClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $resourcePath;

    /**
     * @var string|null
     */
    protected $parentId;

    /**
     * @param PetjeafApiClient $api
     */
    public function __construct(PetjeafApiClient $api)
    {
        $this->client = $api;
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function buildQueryString(array $parameters)
    {
        if (empty($parameters)) {
            return "";
        }

        foreach ($parameters as $key => $value) {
            if ($value === true) {
                $parameters[$key] = "true";
            }

            if ($value === false) {
                $parameters[$key] = "false";
            }
        }

        return "?" . http_build_query($parameters, "", "&");
    }

    /**
     * @param array $body
     * @param array $parameters
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_create(array $body, array $parameters)
    {
        $result = $this->client->httpCall(
            self::REST_CREATE,
            $this->getResourcePath() . $this->buildQueryString($parameters),
            $this->parseRequestBody($body)
        );

        return $result;
    }

    /**
     * Retrieves a single object from the REST API.
     *
     * @param string $id Id of the object to retrieve.
     * @param array $parameters
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_read($id, array $parameters)
    {
        if (empty($id)) {
            throw new ApiException("Invalid resource id.");
        }

        $id = urlencode($id);
        $result = $this->client->httpCall(
            self::REST_READ,
            "{$this->getResourcePath()}/{$id}" . $this->buildQueryString($parameters)
        );

        return $result;
    }

    /**
     * Sends a DELETE request to a single API object.
     *
     * @param string $id
     * @param array $body
     *
     * @return BaseResource
     * @throws ApiException
     */
    protected function rest_delete($id, array $body = [])
    {
        if (empty($id)) {
            throw new ApiException("Invalid resource id.");
        }

        $id = urlencode($id);
        $result = $this->client->httpCall(
            self::REST_DELETE,
            "{$this->getResourcePath()}/{$id}",
            $this->parseRequestBody($body)
        );

        if ($result === null) {
            return null;
        }

        return $result;
    }

    /**
     * Get a collection of objects from the REST API.
     *
     * @param string $from The first resource ID you want to include in your list.
     * @param int $limit
     * @param array $parameters
     *
     * @return BaseCollection
     * @throws ApiException
     */
    protected function rest_list($from = null, $limit = null, array $parameters)
    {
        $parameters = array_merge(["from" => $from, "limit" => $limit], $parameters);

        $apiPath = $this->getResourcePath() . $this->buildQueryString($parameters);

        $result = $this->client->httpCall(self::REST_LIST, $apiPath);

        return $result;
    }

    /**
     * @param string $resourcePath
     */
    public function setResourcePath($resourcePath)
    {
        $this->resourcePath = strtolower($resourcePath);
    }

    /**
     * @return string
     * @throws ApiException
     */
    public function getResourcePath()
    {
        if (strpos($this->resourcePath, "_") !== false) {
            list($parentResource, $childResource) = explode("_", $this->resourcePath, 2);

            if (empty($this->parentId)) {
                throw new ApiException("Subresource '{$this->resourcePath}' used without parent '$parentResource' ID.");
            }

            return "$parentResource/{$this->parentId}/$childResource";
        }

        return $this->resourcePath;
    }

    /**
     * @param array $body
     * @return null|string
     */
    protected function parseRequestBody(array $body)
    {
        if (empty($body)) {
            return null;
        }

        try {
            $encoded = \GuzzleHttp\json_encode($body);
        } catch (\InvalidArgumentException $e) {
            throw new ApiException("Error encoding parameters into JSON: '".$e->getMessage()."'.");
        }

        return $encoded;
    }
}