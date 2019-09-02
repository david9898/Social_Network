<?php


namespace AppBundle\Service;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;

class UserService
{
    private $doctrine;
    private $redis;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;

        $this->redis = new Client([
            'scheme'   => 'tcp',
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'async'    => true
        ]);
    }

    public function getMoreFriends($realCsrfToken, $list, $name, $csrfToken, $currentId)
    {
        $list      = htmlspecialchars($list);
        $name      = htmlspecialchars($name);

        if ( $realCsrfToken === $csrfToken ) {
            $arr = [
                'status'    => 'success',
                'last'      => 'true',
                'currentId' => $currentId
            ];

            $data = $this->doctrine
                        ->getRepository(User::class)
                        ->findUsersByEmailOrName($name, $list);

            if ( count($data) > 20 ) {
                $arr['last'] = 'falce';
            }
            $arr['data'] = $data;

            return $arr;

        }else {
            $arr = [
              'status' => 'error',
              'error'  => 'Wrong CSRFToken'
            ];

            return $arr;
        }
    }

    public function checkForUserRelation($myId, $otherId)
    {
        $responce = [];

        $pipeline = $this->redis->pipeline();

        $pipeline->zrank('friends: ' . $myId, $otherId);
        $pipeline->zrank('suggestionTo: ' . $myId, $otherId);
        $pipeline->zrank('suggestionFrom: ' . $myId, $otherId);
        $pipeline->zrank('follow: ' . $myId, $otherId);

        $responce = $pipeline->execute();

        if ( $responce[0] !== null ) {
            $responce['friendShip'] = 'friend';
        }else if ( $responce[1] !== null ) {
            $responce['friendShip'] = 'sendToMe';
        }else if ( $responce[2] !== null ) {
            $responce['friendShip'] = 'sendFromMe';
        }else {
            $responce['friendShip'] = 'add';
        }

        if ( $responce[3] !== null ) {
            $responce['follow'] = true;
        }else {
            $responce['follow'] = false;
        }

        return $responce;
    }

    public function registerUser($id, $email, $firstName, $lastName, $phone, $birthDay, $profileImage, $fullName, $coverImage)
    {
        $pipeline = $this->redis->pipeline();

        $pipeline->hset('user: ' . $id, 'email', $email);
        $pipeline->hset('user: ' . $id, 'firstName', $firstName);
        $pipeline->hset('user: ' . $id, 'lastName', $lastName);
        $pipeline->hset('user: ' . $id, 'phone', $phone);
        $pipeline->hset('user: ' . $id, 'birthDate', $birthDay);
        $pipeline->hset('user: ' . $id, 'profileImage', $profileImage);
        $pipeline->hset('user: ' . $id, 'fullName', $fullName);
        $pipeline->hset('user: ' . $id, 'coverImage', $coverImage);
        $pipeline->hset('user: ' . $id, 'posts', 0);
        $pipeline->hset('user: ' . $id, 'role', 'ROLE_USER');
        $pipeline->hset('user: ' . $id, 'follow', 0);
        $pipeline->hset('user: ' . $id, 'followers', 0);
        $pipeline->hset('user: ' . $id, 'friends', 0);
        $pipeline->hset('user: ' . $id, 'suggestionTo', 0);
        $pipeline->hset('user: ' . $id, 'suggestionFrom', 0);
        $pipeline->hset('user: ' . $id, 'bann', 0);
        $pipeline->hset('user: ' . $id, 'dateRegister', time());
        $pipeline->hset('user: ' . $id, 'bannDate', 0);
        $pipeline->hset('user: ' . $id, 'unBannDate', 0);
        $pipeline->hset('user: ' . $id, 'unreadMsg', 0);

        $pipeline->execute();

        return true;
    }

    public function getUserData($id)
    {
        $pipeline = $this->redis->pipeline();

        $pipeline->hget('user: ' . $id, 'email');
        $pipeline->hget('user: ' . $id, 'profileImage');
        $pipeline->hget('user: ' . $id, 'firstName');
        $pipeline->hget('user: ' . $id, 'lastName');
        $pipeline->hget('user: ' . $id, 'phone');
        $pipeline->hget('user: ' . $id, 'birthDate');

        $responce = $pipeline->execute();

        $arr                 = [];
        $arr['email']        = $responce[0];
        $arr['profileImage'] = $responce[1];
        $arr['firstName']    = $responce[2];
        $arr['lastName']     = $responce[3];
        $arr['phone']        = $responce[4];
        $arr['birthDate']    = $responce[5];

        return $arr;
    }

    public function getFriendsData($id)
    {
        $friends = $this->redis->zrange('friends: ' . $id, 0, -1);

        $pipeline = $this->redis->pipeline();

        foreach ( $friends as $friend ) {
            $pipeline->hmget('user: ' . $friend, ['email', 'firstName', 'lastName', 'profileImage', 'id']);
        }

        $responce = $pipeline->execute();

        $arr = [];
        foreach ($responce as $item) {
            $arr[] = [
                'email'        => $item[0],
                'firstName'    => $item[1],
                'lastName'     => $item[2],
                'profileImage' => $item[3],
                'id'           => $item[4]
            ];
        }

        return $arr;
    }
}