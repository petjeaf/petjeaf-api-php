<?php

namespace Petjeaf\Api\Endpoints;

class MembershipRewardEndpoint extends EndpointAbstract
{
    protected $resourcePath = "memberships_rewards";

    /**
     * Retrieves a collection of Rewards by  Membership
     *
     * @param string $membershipId
     * @param string $from
     * @param int $limit
     * @param array $parameters
     *
     * @return PaymentCollection
     * @throws ApiException
     */
    public function byMembership($membershipId, $from = null, $limit = null, array $parameters = [])
    {   
        $this->parentId = $membershipId;

        return $this->rest_list($from, $limit, $parameters);
    }

    /**
     * @param string $membershiId
     * @param string $rewardId
     * @param array $parameters
     *
     * @return $result
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    public function getForId($membershipId, $rewardId, array $parameters = [])
    {
        $this->parentId = $membershipId;

        return parent::rest_read($rewardId, $parameters);
    }
}