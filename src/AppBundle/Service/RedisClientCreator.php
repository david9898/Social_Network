<?php


namespace AppBundle\Service;


use Predis\Client;

class RedisClientCreator
{
    /**
     * @var Client
     */
    private $redisClient;

    public function __construct($scheme, $host, $port)
    {
        $this->redisClient = new Client([
            'scheme'     => $scheme,
            'host'       => $host,
            'port'       => $port,
            'async'      => true,
            'persistent' => true
        ]);
    }

    public function getRedisClient()
    {
        return $this->redisClient;
    }
}