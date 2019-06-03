<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Message;
use AppBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller
{

    /**
     * @Route("/messages", name="message")
     */
    public function showMessages(UserService $userService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrfToken', $csrfToken);

        $currentId = $this->getUser()->getId();

        $friends = $userService->getFriendsData($currentId);

        /** @var Message[] $messages */
        $messages = $this->getDoctrine()
                        ->getRepository(Message::class)
                        ->getAllMessagesWhereNotSee($currentId);

        $countMessages = [];
        foreach ($messages as $message) {
            if ( array_key_exists($message->getSendUser()->getId(), $countMessages) ) {
                $countMessages[$message->getSendUser()->getId()]++;
            }else {
                $countMessages[$message->getSendUser()->getId()] = 1;
            }
        }

        return $this->render('messages/showMessages.html.twig', [
            'users' => $friends,
            'csrfToken' => $csrfToken,
            'messages' => $countMessages,
        ]);
    }

    private function mergeArrays(array $arr)
    {
        $array = [];

        foreach ($arr as $item) {
            foreach ($item as $value) {
                $array[] = $value;
            }
        }

        return $array;
    }
}
