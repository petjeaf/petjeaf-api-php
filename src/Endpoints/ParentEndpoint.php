<?php

namespace Petjeaf\Api\Endpoints;

class ParentEndpoint extends EndpointAbstract
{
    /**
     * Retrieves a collection of resources
     *
     * @param string $from
     * @param int $limit
     * @param array $parameters
     *
     * @return $result
     * @throws ApiException
     */
    public function list($from = null, $limit = null, array $parameters = [])
    {
        return $this->rest_list($from, $limit, $parameters);
    }

    /**
     * Retrieve a single resource
     *
     * @param string $resourceId
     * @param array $parameters
     *
     * @return $result
     * @throws ApiException
     */
    public function get($resourceId, array $parameters = [])
    {   
        return $this->rest_read($resourceId, $parameters);
    }
}