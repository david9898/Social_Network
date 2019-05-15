<?php


namespace AppBundle\Service;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getMoreFriends($realCsrfToken, $list, $name, $csrfToken)
    {
        $list      = htmlspecialchars($list);
        $name      = htmlspecialchars($name);

        if ( $realCsrfToken === $csrfToken ) {
            $arr = ['status' => 'success'];

            $data = $this->doctrine
                        ->getRepository(User::class)
                        ->findUsersByEmailOrName($name, $list);

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
}