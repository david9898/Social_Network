<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\UserService;
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

    private $userService;

    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    /**
     * @Route("/findMoreFriends/{list}/{csrfToken}/{name}")
     * @Method("GET")
     *
     */
    public function getMoreUsers($list, $csrfToken, $name)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session           = new Session();
        $realCsrfToken     = $session->get('csrf_token');
        $myId              = $session->get('currentId');

        $service = $this->userService->getMoreFriends($realCsrfToken, $list, $name, $csrfToken, $myId);

        return $this->JsonResponce($service);
    }

    /**
     * @Route("/searchFriends/{csrfToken}/{name}")
     * @Method("GET")
     * @param $csrfToken
     * @param $name
     */
    public function searchFriends($csrfToken, $name)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session       = new Session();
        $realCsrfToken = $session->get('csrf_token');

        if ( $csrfToken === $realCsrfToken ) {
            $users = $this->getDoctrine()
                            ->getRepository(User::class)
                            ->searchUsers($name);

            $responce = [
                'status' => 'success',
                'users'  => $users
            ];

            return $this->json($responce);
        }else {
            $responce = [
                'status'      => 'error',
                'description' => 'Wrong token!'
            ];

            return $this->JsonResponce($responce);
        }

    }

    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
