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

    public function getMoreFriends($list, $friendsAndSuggestion)
    {
        $lastUserId = $this->doctrine
                            ->getRepository(User::class)
                            ->getLastUserId();
    }
}