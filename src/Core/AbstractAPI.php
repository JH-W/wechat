<?php
/**
 * @Author: binghe
 * @Date:   2017-06-09 14:35:13
 * @Last Modified by:   binghe
 * @Last Modified time: 2017-06-22 12:43:04
 */
namespace Binghe\Wechat\Core;

use Binghe\Wechat\Core\Exceptions\HttpException;
use Binghe\Wechat\Support\Collection;
use Binghe\Wechat\Support\Log;
use Binghe\Wechat\Support\Http;
use Binghe\Wechat\Support\Language;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractAPI.
 */
abstract class AbstractAPI
{
    public $language;
    /**
     * Http instance.
     *
     * @var \Binghe\Wechat\Core\Http
     */
    protected $http;

    /**
     * The request token.
     *
     * @var \Binghe\Wechat\Core\AccessToken
     */
    protected $accessToken;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';

    /**
     * Constructor.
     *
     * @param \Binghe\Wechat\Core\AuthorizerAccessToken $accessToken
     */
    public function __construct(AuthorizerAccessToken $accessToken,$language='zh_cn')
    {
        $this->setAccessToken($accessToken);
        $this->language=$language;
    }

    /**
     * Return the http instance.
     *
     * @return \Binghe\Wechat\Core\Http
     */
    public function getHttp()
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (count($this->http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }

        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param \Binghe\Wechat\Core\Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Return the current accessToken.
     *
     * @return \Binghe\Wechat\Core\AuthorizerAccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the request token.
     *
     * @param \Binghe\Wechat\Core\AuthorizerAccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AuthorizerAccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param string $method
     * @param array  $args
     *
     * @return \Binghe\Wechat\Support\Collection
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();

        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));

        $this->checkAndThrow($contents);

        return new Collection($contents);
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // log
        $this->http->addMiddleware($this->logMiddleware());
        // retry
        $this->http->addMiddleware($this->retryMiddleware());
        // access token
        $this->http->addMiddleware($this->accessTokenMiddleware());
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $field = $this->accessToken->getQueryName();
                $token = $this->accessToken->getToken();

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), $field, $token));

                return $handler($request, $options);
            };
        };
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} ".json_encode($options));
            Log::debug('Request headers:'.json_encode($request->getHeaders()));
        });
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
                                          $retries,
                                          RequestInterface $request,
                                          ResponseInterface $response = null
                                       ) {
            // Limit the number of retries to 2
            if ($retries <= 2 && $response && $body = $response->getBody()) {
                // Retry on server errors
                if (stripos($body, 'errcode') && (stripos($body, '40001') || stripos($body, '42001'))) {
                    $field = $this->accessToken->getQueryName();
                    $token = $this->accessToken->getToken(true);

                    $request = $request->withUri($newUri = Uri::withQueryValue($request->getUri(), $field, $token));

                    Log::debug("Retry with Request Token: {$token}");
                    Log::debug("Retry with Request Uri: {$newUri}");

                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     *
     * @throws \Binghe\Wechat\Core\Exceptions\HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['errcode']) && 0 !== $contents['errcode']) {
            if (empty($contents['errmsg'])) {
                $contents['errmsg'] = 'Unknown';
            }
            $errMsg=Language::getMessage($contents['errcode'],$contents['errmsg'],$this->language);
            throw new HttpException($errMsg, $contents['errcode']);
        }
    }
}