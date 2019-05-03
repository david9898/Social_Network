<?php


namespace AppBundle\Ratchet;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface
{
    private $pdo;
    protected $clients;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->clients = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients[$conn->Session->get('currentId')] = $conn;
        echo "New connection! {$conn->Session->get('currentId')}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $myId = $from->Session->get('currentId');
        $msgContent = json_decode($msg, true);
        switch ( $msgContent['command'] ) {
            case 'addMessage':
                $this->addMessage($myId, $msgContent, $from);
                break;

            case 'seenMessage':
                $this->seenMessage($msgContent, $myId);
                break;

            case 'seeMessageBetweenUsers':
                $this->seeMessagesBetweenUsers($myId, $msgContent);
                break;

            case 'seeCertainMessage':
                $this->getCertainMessage($myId, $msgContent);
                break;

        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->clients[$conn->Session->get('currentId')]);
        $conn->close();
        echo "Connection {$conn->Session->get('currentId')} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        unset($this->clients[$conn->Session->get('currentId')]);
        $conn->close();
    }

    private function addMessage($myId, $msgContent, $conn) {
        $id                     = $this->insertMessage($myId, $msgContent);
        $randomId               = $msgContent['id'];
        $msgContent['id']       = $id;
        $msgContent['randomId'] = $randomId;
        $msgContent['messageStatus'] = 'onlySaved';
        if ( isset($this->clients[$msgContent['acceptUser']]) ) {
            $msgContent['messageStatus'] = 'delivered';
            $msgContent['command']   = 'addMessage';
            $msgContent['sendUser']  = $myId;
            $this->clients[$msgContent['acceptUser']]->send(json_encode($msgContent));
        }
        $msgContent['command'] = 'savedMsg';
        $conn->send(json_encode($msgContent));
    }


    private function insertMessage($myId, $msg)
    {
        $acceptUser = htmlspecialchars($msg['acceptUser']);
        $dateNow = htmlspecialchars(date('Y-m-d H:i:s'));
        $content = htmlspecialchars($msg['content']);
        $sql = 'INSERT INTO messages (`accept_user`, `send_user`, `content`, `date_added`, `is_delivered`, `is_seen`)
                VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $this->pdo->prepare($sql);

        if ( isset($this->clients[$acceptUser]) ) {
            $stmt->execute([$acceptUser, $myId, $content, $dateNow, 1, 0]);
        }else {
            $stmt->execute([$acceptUser, $myId, $content, $dateNow, 0, 0]);
        }
        return $this->pdo->lastInsertId();
    }

    private function seenMessage($msg)
    {
        $msgId      = $msg['id'];
        $sendUser   = $msg['sendUser'];
        $acceptUser = $msg['acceptUser'];
        $sql        = 'UPDATE messages SET is_seen = 1 WHERE id = ?';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$msgId]);

        if ( isset($this->clients[$sendUser]) ) {
            $arr = [
                'command'    => 'seenMessage',
                'sendUser'   => $sendUser,
                'id'         => $msgId,
                'acceptUser' => $acceptUser
            ];

            $this->clients[$sendUser]->send(json_encode($arr));
        }
    }

    private function seeMessagesBetweenUsers($myId, $msg)
    {
        $otherId = $msg['otherId'];

        if ( isset($this->clients[$otherId]) ) {
            $this->clients[$otherId]->send(json_encode([
                'command' => 'seeMessageBetweenUsers',
                'otherId' => $myId
            ]));
        }
    }

    private function getCertainMessage($myId, $msg)
    {
        $msgId = $msg['id'];
        $currentUser = $msg['currentIdOnMessenger'];

        $sql = 'SELECT * FROM messages WHERE id = ?';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$msgId]);
        $messageInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ( $messageInfo['is_delivered'] == 1 ) {
            if ( $messageInfo['is_seen'] == 1 ) {
                if ( isset($this->clients[$myId]) ) {
                    $arr = [
                        'command'   => 'seeCertainMessage',
                        'otherId'   => $currentUser,
                        'msgStatus' => 'seen'
                    ];

                    $this->clients[$myId]->send(json_encode($arr));
                }
            }else {
                if ( isset($this->clients[$myId]) ) {
                    $arr = [
                        'command'   => 'seeCertainMessage',
                        'otherId'   => $currentUser,
                        'msgStatus' => 'delivered'
                    ];

                    $this->clients[$myId]->send(json_encode($arr));
                }
            }
        }else {
            if ( isset($this->clients[$myId]) ) {
                $arr = [
                    'command'   => 'seeCertainMessage',
                    'otherId'   => $currentUser,
                    'msgStatus' => 'saved'
                ];

                $this->clients[$myId]->send(json_encode($arr));
            }
        }
    }
}