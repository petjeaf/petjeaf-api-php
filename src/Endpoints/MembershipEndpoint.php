<?php

namespace Petjeaf\Api\Endpoints;

use Petjeaf\Api\Exceptions\ApiException;

class MembershipEndpoint extends ParentEndpoint
{
    protected $resourcePath = "memberships";

    /**
     * Retrieves a collection of Memberships by a page ID
     *
     * @param string $pageId
     * @param string $from
     * @param int $limit
     * @param array $parameters
     *
     * @return PaymentCollection
     * @throws ApiException
     */
    public function byPage($pageId, $from = null, $limit = null, array $parameters = [])
    {   
        $parameters = array_merge(['pageId' => $pageId], $parameters);

        return $this->list($from, $limit, $parameters);
    }
}