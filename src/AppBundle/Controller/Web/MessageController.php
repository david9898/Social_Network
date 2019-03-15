<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller
{

    /**
     * @Route("/messages", name="message")
     */
    public function showMessages()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrfToken', $csrfToken);

        /** @var User $user */
        $user = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->getFullUser($this->getUser()->getId());

        $friends = $user->getAllFriends();

        return $this->render('messages/showMessages.html.twig', [
            'users' => $friends,
            'csrfToken' => $csrfToken,
        ]);
    }
}
