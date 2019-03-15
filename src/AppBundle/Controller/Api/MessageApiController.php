<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Message;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessageApiController
 * @package AppBundle\Controller\Api
 *
 * @Route("/api")
 */
class MessageApiController extends Controller
{

    /**
     * @Route("/sendMessage")
     */
    public function sendMessage(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();
        $csrfToken = $session->get('csrfToken');

        $content = $request->getContent();
        $realData = json_decode($content, true);

        if ( $realData['csrfToken'] === $csrfToken ) {
            $message = new Message();

            $acceptUser = $this->getDoctrine()
                                ->getRepository(User::class)
                                ->findOneBy(['id' => (int)$realData['acceptUser']]);

            $message->setContent($realData['content']);
            $message->setSendUser($this->getUser());
            $message->setAcceptUser($acceptUser);

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            return $this->JsonResponce(['status' => 'success']);
        }
    }

    /**
     * @Route("/getMessagesBetweenUsers/{otherUserId}/{csrfToken}")
     */
    public function getMessagesBetweenUsers($otherUserId, $csrfToken)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();
        $realCsrfToken = $session->get('csrfToken');

        if ( $realCsrfToken === $csrfToken ) {

            $currentId = $this->getUser()->getId();

            $messages = $this->getDoctrine()
                            ->getRepository(Message::class)
                            ->getMessagesWithSomeone($currentId, $otherUserId);

            $responce = [];
            $responce['status'] = 'success';
            $responce['userId'] = $currentId;

            if ( count($messages) > 0 ) {
                $responce['responce'] = $messages;
            }else {
                $responce['responce'] = 'none';
            }
            return $this->JsonResponce($responce);
        }
    }

    /**
     * @Route("/getMoreMessages/{csrfToken}/{userId}/{list}")
     */
    public function getMoreMessages($csrfToken, $userId, $list)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();
        $realCsrfToken = $session->get('csrfToken');

        if ( $realCsrfToken === $csrfToken ) {
            $currentId = $this->getUser()->getId();

            $firstMessage = $list * 20;

            $messages = $this->getDoctrine()
                            ->getRepository(Message::class)
                            ->getMessagesWithSomeone($currentId, $userId, $firstMessage);

            $responce = [];
            $responce['status'] = 'success';
            $responce['currentId'] = $currentId;
            $responce['messages'] = $messages;

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
