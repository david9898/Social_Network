<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserApiController
 * @package AppBundle\Controller\Api
 * @Route("/api")
 */
class UserApiController extends Controller
{

    /**
     * @Route("/findMoreFriends/{list}")
     * @Method("GET")
     *
     */
    public function getMoreUsers($list)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $CU = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->getCountUsers();

        $countUsers = (int)$CU[1];

        /** @var User $currentUser */
        $currentUser = $this->getDoctrine()
                            ->getRepository(User::class)
                            ->getFullUser($this->getUser()->getId());

        $currentId = $currentUser->getId();

        $friendsId = $session->get('friends');

        $suggestions = $this->getDoctrine()
                            ->getRepository(Suggestion::class)
                            ->getMySendSuggestions($currentId);

        foreach ($suggestions as $suggestion) {
            /** @var Suggestion $suggestion */
            if ( $suggestion->getAcceptUser()->getId() != $currentId ) {
                $friendsId[] = $suggestion->getAcceptUser()->getId();
            }
        }

        foreach ($suggestions as $suggestion) {
            /** @var Suggestion $suggestion */
            if ( $suggestion->getSuggestUser()->getId() != $currentId ) {
                $friendsId[] = $suggestion->getSuggestUser()->getId();
            }
        }

        $users = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->findUsersBySearch($list, $friendsId);

        $lastUser = $users[count($users) - 1]->getId();

        if ( $lastUser >= $countUsers ) {
            $responce = ['users' => $users, 'last' => 'true', 'count' => $countUsers, 'lastUser' => $lastUser];

            $serializer = $this->container->get('jms_serializer');
            $json = $serializer->serialize($responce, 'json');

            return new JsonResponse($json,
                Response::HTTP_OK, array('content-type' => 'application/json'));
        }

        $responce = ['users' => $users];
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($responce, 'json');

        return new JsonResponse($json,
                    Response::HTTP_OK, array('content-type' => 'application/json'));
    }

    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
