<?php

namespace Petjeaf\Api\Endpoints;

class PageRewardEndpoint extends EndpointAbstract
{
    protected $resourcePath = "pages_rewards";

    /**
     * Retrieves a collection of Rewards by Page
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
        $this->parentId = $pageId;

        return $this->rest_list($from, $limit, $parameters);
    }

    /**
     * @param string $pageId
     * @param string $rewardId
     * @param array $parameters
     *
     * @return $result
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    public function getForId($pageId, $rewardId, array $parameters = [])
    {
        $this->parentId = $pageId;

        return parent::rest_read($rewardId, $parameters);
    }
}