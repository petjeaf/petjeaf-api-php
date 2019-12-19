<?php

namespace Petjeaf\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Petjeaf\Api\Endpoints\MembershipEndpoint;
use Petjeaf\Api\Endpoints\MembershipRewardEndpoint;
use Petjeaf\Api\Endpoints\PageEndpoint;
use Petjeaf\Api\Endpoints\PageRewardEndpoint;
use Petjeaf\Api\Endpoints\PagePlanEndpoint;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Petjeaf\Api\Exceptions\ApiException;

class PetjeafApiClient
{
    /**
     * Endpoint of the remote API.
     */
    const API_ENDPOINT = 'https://api.petje.af/v1';

    /**
     * HTTP Methods
     */
    const HTTP_GET = "GET";
    const HTTP_POST = "POST";
    const HTTP_DELETE = "DELETE";
    const HTTP_PATCH = "PATCH";

    /**
     * HTTP status codes
     */
    const HTTP_NO_CONTENT = 204;

    /**
     * Default response timeout (in seconds).
     */
    const TIMEOUT = 10;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiEndpoint = self::API_ENDPOINT;

    /**
     * Memberships resource.
     *
     * @var MembershipEndpoint
     */
    public $memberships;

    /**
     * Membership rewards resource.
     *
     * @var MembershipRewardEndpoint
     */
    public $membershipRewards;

    /**
     * Pages resource.
     *
     * @var PageEndpoint
     */
    public $pages;

    /**
     * Page plans resource.
     *
     * @var PagePlanEndpoint
     */
    public $pagePlans;

    /**
     * Page rewards resource.
     *
     * @var PageRewardEndpoint
     */
    public $pageRewards;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @param ClientInterface $httpClient
     *
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?
            $httpClient :
            new Client([
                \GuzzleHttp\RequestOptions::VERIFY => \Composer\CaBundle\CaBundle::getBundledCaBundlePath(),
                \GuzzleHttp\RequestOptions::TIMEOUT => self::TIMEOUT,
            ]);

        $this->memberships = new MembershipEndpoint($this);
        $this->membershipRewards = new MembershipRewardEndpoint($this);
        $this->pages = new PageEndpoint($this);
        $this->pagePlans = new PagePlanEndpoint($this);
        $this->pageRewards = new PageRewardEndpoint($this);
    }

    /**
     * @param string $url
     *
     * @return PetjeafApiClient
     */
    public function setApiEndpoint($url)
    {
        $this->apiEndpoint = rtrim(trim($url), '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    /**
     * @param string $accessToken
     *
     * @return PetjeafApiClient
     */
    public function setAccessToken($accessToken)
    {
        $accessToken = trim($accessToken);

        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Perform an http call.
     *
     * @param string $httpMethod
     * @param string $apiMethod
     * @param string|null|resource|StreamInterface $httpBody
     *
     * @return \stdClass
     * @throws ApiException
     *
     * @codeCoverageIgnore
     */
    public function httpCall($httpMethod, $apiMethod, $httpBody = null)
    {
        $url = $this->apiEndpoint . "/" . $apiMethod;
        return $this->httpCallToFullUrl($httpMethod, $url, $httpBody);
    }

    /**
     * Perform an http call to a full url.
     *
     * @param string $httpMethod
     * @param string $url
     * @param string|null|resource|StreamInterface $httpBody
     *
     * @return \stdClass|null
     * @throws ApiException
     *
     * @codeCoverageIgnore
     */
    public function httpCallToFullUrl($httpMethod, $url, $httpBody = null)
    {
        if (empty($this->accessToken)) {
            throw new ApiException("You have not set an access token. Please use setAccessToken() to set the access token.");
        }

        $headers = [
            'Accept' => "application/json",
            'Authorization' => "Bearer {$this->accessToken}",
            'User-Agent' => " OAuth/2.0",
        ];
        $request = new Request($httpMethod, $url, $headers, $httpBody);
        try {
            $response = $this->httpClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw ApiException::createFromGuzzleException($e);
        }
        if (!$response) {
            throw new ApiException("Did not receive API response.");
        }
        return $this->parseResponseBody($response);
    }

    /**
     * Parse the PSR-7 Response body
     *
     * @param ResponseInterface $response
     * @return \stdClass|null   
     * @throws ApiException
     */
    private function parseResponseBody(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        if (empty($body)) {
            if ($response->getStatusCode() === self::HTTP_NO_CONTENT) {
                return null;
            }
            throw new ApiException("No response body found.");
        }

        $object = @json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Unable to decode response: '{$body}'.");
        }

        if ($response->getStatusCode() >= 400) {
            throw ApiException::createFromResponse($response);
        }

        return $object;
    }
}