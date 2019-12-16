<?php

namespace Petjeaf\Api\Exceptions;

use GuzzleHttp\Psr7\Response;
use Throwable;

class ApiException extends \Exception
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param string $message
     * @param int $code
     * @param string|null $field
     * @param \GuzzleHttp\Psr7\Response|null $response
     * @param \Throwable|null $previous
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    public function __construct(
        $message = "",
        $code = 0,
        $field = null,
        Response $response = null,
        Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param \GuzzleHttp\Exception\RequestException $guzzleException
     * @param \Throwable $previous
     * @return \Petjeaf\Api\Exceptions\ApiException
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    public static function createFromGuzzleException($guzzleException, Throwable $previous = null)
    {
        // Not all Guzzle Exceptions implement hasResponse() / getResponse()
        if(method_exists($guzzleException, 'hasResponse') && method_exists($guzzleException, 'getResponse')) {
            if($guzzleException->hasResponse()) {
                return static::createFromResponse($guzzleException->getResponse());
            }
        }
        return new static($guzzleException->getMessage(), $guzzleException->getCode(), null, $previous);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Throwable|null $previous
     * @return \Petjeaf\Api\Exceptions\ApiException
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    public static function createFromResponse($response, Throwable $previous = null)
    {
        $object = static::parseResponseBody($response);
        $field = null;
        if (!empty($object->field)) {
            $field = $object->field;
        }
        return new static(
            "Error executing API call ({$object->status}: {$object->title}): {$object->detail}",
            $response->getStatusCode(),
            $field,
            $response,
            $previous
        );
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Petjeaf\Api\Exceptions\ApiException
     */
    protected static function parseResponseBody($response)
    {
        $body = (string) $response->getBody();
        $object = @json_decode($body);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new static("Unable to decode response: '{$body}'.");
        }
        return $object;
    }
}
