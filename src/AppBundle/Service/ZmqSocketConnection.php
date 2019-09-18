<?php


namespace AppBundle\Service;


class ZmqSocketConnection
{
    private $zmqContext;
    private $zmqSocket;

    public function __construct($zmqScheme, $zmqHost, $zmqPort)
    {
        $this->zmqContext = new \ZMQContext(1);

        $this->zmqSocket = $this->zmqContext->getSocket(\ZMQ::SOCKET_PUSH);
        $this->zmqSocket->connect($zmqScheme . '://' . $zmqHost . ':' .  $zmqPort);
    }

    public function sendToWebSocket($arr)
    {
        $this->zmqSocket->send(json_encode($arr));
    }

}