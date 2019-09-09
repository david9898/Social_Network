<?php

namespace AppBundle\Command;

use AppBundle\Ratchet\Chat;
use Ratchet\App;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use React\ZMQ\Context;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class StartWebSocketAndZmqCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('startWebSocketAndZmq')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $myIP = $this->getContainer()->getParameter('database_host');
        $pdo = new \PDO('mysql:host=' . $myIP . ';dbname=symfony_social_network;charset=utf8', 'root', null);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $dbOptions = array(
            'db_table'      => 'sessions',
            'db_id_col'     => 'sess_id',
            'db_data_col'   => 'sess_data',
            'db_time_col'   => 'sess_time',
            'lock_mode'     => 0,
        );

        $pdoProvider = new PdoSessionHandler($pdo, $dbOptions);

        $loop = Factory::create();
        $vid = new Chat($pdo);

        $context = new Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:5555');
        $pull->on('message', array($vid, 'handleZmqMessage'));

        $session = new SessionProvider(
            new WsServer(
                $vid
            ),
            $pdoProvider
        );

        $server = new App("192.168.0.100", 9899, '0.0.0.0');
        $server->route('/{something}', $session, array('*'));
        $server->route('/{something}/{otherThing}', $session, array('*'));
        $server->run();
    }
}
