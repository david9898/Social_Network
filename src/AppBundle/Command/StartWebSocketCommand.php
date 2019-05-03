<?php

namespace AppBundle\Command;

use AppBundle\Ratchet\Chat;
use Ratchet\Session\SessionProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class StartWebSocketCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('websocket/start')
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

        $session = new SessionProvider(
            new WsServer(
                new Chat($pdo)
            ),
            $pdoProvider
        );

        $server = IoServer::factory(
            new HttpServer(
                $session
            ),
            9899
        );

        $server->run();
    }
}
