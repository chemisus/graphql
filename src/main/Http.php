<?php

namespace Chemisus\GraphQL;

use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;

class Http
{
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * @var Client
     */
    private static $client;

    public static function init(LoopInterface $loop)
    {
        self::$client = new Client($loop);
    }

    public static function get($url, $headers = [], callable $config = null)
    {
        if (!self::$client) {
            return new FulfilledPromise(file_get_contents($url));
        }

        $deferred = new Deferred();
        $request = self::$client->request('GET', $url, $headers);
        $request->on('response', function (Response $response) use ($deferred) {
            $deferred->notify($response->getHeaders());

            $response->on('data', function ($chunk) use (&$data, $deferred) {
                $data .= $chunk;
                $deferred->notify($chunk);
            });

            $response->on('end', function () use (&$data, $deferred) {
                $deferred->resolve($data);
            });

            $response->on('error', function ($error) use (&$data, $deferred) {
                $deferred->reject($error);
            });
        });

        $request->on('error', function ($error) use ($deferred) {
            $deferred->reject($error);
        });

        if (is_callable($config)) {
            call_user_func($config, $request);
        }
        $request->end();
        return $deferred->promise();
    }

    public static function post($url, $data, $type = self::CONTENT_TYPE_URLENCODED, $headers = [], callable $config = null)
    {
        $headers['Content-Length'] = strlen($data);
        $headers['Content-Type'] = $type;

        $deferred = new Deferred();
        $request = self::$client->request('POST', $url, $headers);
        $request->on('response', function (Response $response) use ($deferred) {
            $deferred->notify($response->getHeaders());

            $response->on('data', function ($chunk) use (&$data, $deferred) {
                $data .= $chunk;
                $deferred->notify($chunk);
            });

            $response->on('end', function () use (&$data, $deferred) {
                $deferred->resolve($data);
            });

            $response->on('error', function ($error) use (&$data, $deferred) {
                $deferred->reject($error);
            });
        });

        $request->on('error', function ($error) use ($deferred) {
            $deferred->reject($error);
        });

        if (is_callable($config)) {
            call_user_func($config, $request);
        }
        $request->end($data);
        return $deferred->promise();
    }
}
