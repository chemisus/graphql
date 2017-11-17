<?php

namespace Chemisus\GraphQL;

use React\Promise\FulfilledPromise;

class FileCachedHttp extends Http
{
    /**
     * @var string
     */
    public static $root;

    /**
     * @var string
     */
    public static $directory;

    public static function cachePath($url)
    {
        return sprintf('/%s/%s/%s', trim(self::$root, '/'), trim(self::$directory, '/'), base64_encode($url));
    }

    public static function get($url, $headers = [], callable $config = null)
    {
        $path = self::cachePath($url);

        if (file_exists($path)) {
            return new FulfilledPromise(json_decode(file_get_contents($path)));
        }

        return parent::get($url, $headers, $config)
            ->then(function ($data) use ($path) {
                $dir = dirname($path);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($path, $data);
                return json_decode($data);
            });
    }

    public static function post($url, $data, $type = self::CONTENT_TYPE_URLENCODED, $headers = [], callable $config = null)
    {
        return parent::post($url, $data, $type, $headers, $config);
    }
}