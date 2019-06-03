<?php

namespace AppBundle\Service;

use Predis\Client;

class SuggestionService
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'async'    => true
        ]);
    }

    public function getSuggestionToMe($id)
    {
        $suggestionToMe = $this->redis->zrange('suggestionTo: ' . $id, 0, -1);

        $pipeline = $this->redis->pipeline();

        foreach ($suggestionToMe as $user) {
            $pipeline->hmget('user: ' . $user, ['id', 'email', 'fullName', 'profileImage']);
        }

        return $pipeline->execute();
    }

    public function acceptSuggestion($csrfToken, $realCsrfToken, $myId, $otherId)
    {
        if ( $csrfToken === $realCsrfToken ) {
            $suggestionToMe          = $this->redis->zrank('suggestionTo: ' . $myId, $otherId);
            $suggestionFromOtherUser = $this->redis->zrank('suggestionFrom: ' . $otherId, $myId);
            $myFriends               = $this->redis->hget('user: ' . $myId, 'friends');
            $otherUserFriends        = $this->redis->hget('user: ' . $otherId, 'friends');

            if ( $suggestionToMe === null ) {
                return ['status' => 'error', 'description' => 'Suggestion doesn`t exists!!!'];
            }

            if ( $suggestionFromOtherUser === null ) {
                return ['status' => 'error', 'description' => 'Suggestion doesn`t exists!!!'];
            }

            if ( (int)$myFriends >= 1000 ) {
                return ['status' => 'error', 'description' => 'Max limit friends is 1000!!!'];
            }

            if ( (int)$otherUserFriends >= 1000 ) {
                return ['status' => 'error', 'description' => 'Max limit friends is 1000!!!'];
            }

            $pipeline = $this->redis->pipeline();

            $pipeline->zadd('friends: ' . $myId, [$otherId => time()]);
            $pipeline->zadd('followers: ' . $myId, [$otherId => time()]);
            $pipeline->zadd('follow: ' . $myId, [$otherId => time()]);
            $pipeline->zrem('suggestionTo: ' . $myId, $otherId);
            $pipeline->hincrby('user: ' . $myId, 'friends', 1);
            $pipeline->hincrby('user: ' . $myId, 'followers', 1);
            $pipeline->hincrby('user: ' . $myId, 'follow', 1);

            $pipeline->zadd('friends: ' . $otherId, [$myId => time()]);
            $pipeline->zadd('followers: ' . $otherId, [$myId => time()]);
            $pipeline->zadd('follow: ' . $otherId, [$myId => time()]);
            $pipeline->zrem('suggestionFrom: ' . $otherId, $myId);
            $pipeline->hincrby('user: ' . $otherId, 'friends', 1);
            $pipeline->hincrby('user: ' . $otherId, 'followers', 1);
            $pipeline->hincrby('user: ' . $otherId, 'follow', 1);

            $pipeline->hincrby('user: ' . $otherId, 'notifications', 1);


            $pipeline->execute();

            $suggestionFrom = $this->redis->hget('user: ' . $otherId, 'suggestionFrom');
            $suggestionTo   = $this->redis->hget('user: ' . $myId, 'suggestionTo');
            $this->redis->hset('user: ' . $myId, 'suggestionTo', (int)$suggestionTo - 1);
            $this->redis->hset('user: ' . $otherId, 'suggestionFrom', (int)$suggestionFrom - 1);

            $image    = $this->redis->hget('user: ' . $myId, 'profileImage');
            $fullName = $this->redis->hget('user: ' . $myId, 'fullName');
            $arr      = [
                'profileImage'    => $image,
                'fullName'        => $fullName,
                'message'         => ' accept your friendship!!!',
                'href'            => '/profile/' . $myId
            ];

            $countNotifications = $this->redis->lpush('notifications: ' . $otherId, json_encode($arr));

            if ( (int)$countNotifications >= 19 ) {
                $this->redis->rpop('notifications: ' . $otherId);
            }

            $arr['id']      = $otherId;
            $arr['command'] = 'acceptSuggestion';

            $context        = new \ZMQContext(1);
            $socket         = $context->getSocket(\ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://127.0.0.1:5555");
            $socket->send(json_encode($arr));

            return ['status' => 'success'];
        }else {
            return ['status' => 'error', 'description' => 'Wrong CsrfToken!!!'];
        }
    }

    public function addSuggestion($csrfToken, $realCsrfToken, $myId, $otherId)
    {
        if ( $csrfToken === $realCsrfToken ) {

            $pipeline = $this->redis->pipeline();

            $pipeline->zrank('suggestionTo: ' . $myId, $otherId);
            $pipeline->zrank('suggestionFrom: ' . $myId, $otherId);
            $pipeline->zrank('friends: ' . $myId, $otherId);
            $pipeline->zrank('suggestionTo: ' . $otherId, $myId);
            $pipeline->zrank('suggestionFrom: ' . $otherId, $myId);
            $pipeline->zrank('friends: ' . $otherId, $myId);
            $pipeline->hget('user: ' . $myId, 'suggestionFrom');

            $responce = $pipeline->execute();


            if ( (int)$responce[6] >= 50 ) {
                return ['status' => 'error', 'description' => 'You can not send more suggestions!!!'];
            }

            if ( $responce[0] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $responce[1] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $responce[2] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $responce[3] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $responce[4] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $responce[5] !== null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            $pipeline = $this->redis->pipeline();

            $pipeline->hincrby('user: ' . $myId, 'suggestionFrom', 1);
            $pipeline->hincrby('user: ' . $otherId, 'suggestionTo', 1);
            $pipeline->zadd('suggestionFrom: ' . $myId, [$otherId => time()]);
            $pipeline->zadd('suggestionTo: ' . $otherId, [$myId => time()]);

            $pipeline->execute();

            $image    = $this->redis->hget('user: ' . $myId, 'profileImage');
            $fullName = $this->redis->hget('user: ' . $myId, 'fullName');
            $arr      = [
                'profileImage' => $image,
                'fullName'     => $fullName,
                'message'      => ' send you friendship!!!',
                'href'         => '/profile/' . $myId
            ];

            $countNotifications = $this->redis->lpush('notifications: ' . $otherId, json_encode($arr));

            if ( (int)$countNotifications >= 19 ) {
                $this->redis->rpop('notifications: ' . $otherId);
            }

            $arr['id'] = $otherId;
            $arr['command'] = 'addSuggestion';
            $context   = new \ZMQContext(1);
            $socket    = $context->getSocket(\ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://127.0.0.1:5555");
            $socket->send(json_encode($arr));

            return ['status' => 'success'];
        }else {
            return ['status' => 'error', 'description' => 'Wrong csrfToken!!!'];
        }
    }

    public function denySuggestion($csrfToken, $realCsrfToken, $myId, $otherId)
    {
        if ( $csrfToken === $realCsrfToken ) {
            $pipeline = $this->redis->pipeline();

            $pipeline->zrank('suggestionTo: ' . $myId, $otherId);
            $pipeline->zrank('suggestionFrom: ' . $otherId, $myId);
            $pipeline->hget('user: ' . $myId, 'suggestionTo');
            $pipeline->hget('user: ' . $otherId, 'suggestionFrom');

            $exPipeline = $pipeline->execute();

            if ( $exPipeline[0] === null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            if ( $exPipeline[1] === null ) {
                return ['status' => 'error', 'description' => 'Invalid suggestion!!!'];
            }

            $pipeline = $this->redis->pipeline();

            $pipeline->zrem('suggestionTo: ' . $myId, $otherId);
            $pipeline->zrem('suggestionFrom: ' . $otherId, $myId);
            $pipeline->hset('user: ' . $myId, 'suggestionTo',$exPipeline[2] - 1);
            $pipeline->hset('user: ' . $otherId, 'suggestionFrom', $exPipeline[3] - 1);

            $pipeline->execute();

            return ['status' => 'success'];
        }else {
            return ['status' => 'error', 'description' => 'Wrong csrfToken!!!'];
        }
    }

}