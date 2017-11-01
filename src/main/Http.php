<?php

namespace Chemisus\GraphQL;

use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Deferred;

class Http
{
    /**
     * @var Client
     */
    private static $client;

    public static function init(LoopInterface $loop)
    {
        self::$client = new Client($loop);
    }

    public static function get($url, callable $config = null)
    {
        $deferred = new Deferred();
        $request = self::$client->request('GET', $url);
        $request->on('response', function (Response $response) use ($deferred) {
            $response->on('data', function ($chunk) use (&$data) {
                $data .= $chunk;
            });
            $response->on('end', function () use (&$data, $deferred) {
                $deferred->resolve($data);
            });
        });
        if (is_callable($config)) {
            call_user_func($config, $request);
        }
        $request->end();
        return $deferred->promise();
    }

    public static function post($url, $data, callable $config = null)
    {
        $deferred = new Deferred();
        $request = self::$client->request('POST', $url);
        $request->on('response', function (Response $response) use ($deferred) {
            $response->on('data', function ($chunk) use (&$data) {
                $data .= $chunk;
            });
            $response->on('end', function () use (&$data, $deferred) {
                $deferred->resolve($data);
            });
        });
        if (is_callable($config)) {
            call_user_func($config, $request);
        }
        $request->end($data);
        return $deferred->promise();
    }
}
