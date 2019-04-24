<?php


namespace AppBundle\Rpc;


use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

class AcmeRpc implements RpcInterface
{

    public function sum(ConnectionInterface $connection, WampRequest $request, $params)
    {
        return array("result" => array_sum($params));
    }

    public function getName()
    {
        return 'acme.rpc';
    }


}