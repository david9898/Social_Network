<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SuggestionApiController
 * @package AppBundle\Controller\Api
 * @Route("/api")
 */
class SuggestionApiController extends Controller
{

    /**
     * @Route("/addSuggestion")
     * @Method("POST")
     */
    public function addSuggest(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $content = $request->getContent();
        $realData = json_decode($content, true);

        $csrfToken = $session->get('csrf_token');

        $requestedToken = $realData['csrf_token'];

        if ( $csrfToken == $requestedToken ) {
            $count = $this->getDoctrine()
                            ->getRepository(Suggestion::class)
                            ->getCountSuggestions($this->getUser()->getId());

            if ( (int)$count[1] >= 50 ) {
                $responce = ['status' => 'error', 'count' => $count];
                $serializer = $this->container->get('jms_serializer');
                $json = $serializer->serialize($responce, 'json');
                return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
            }

            $targetUser = (int)$realData['target_user'];

            $suggestion = new Suggestion();

            $suggestion->setAcceptUser($this->getDoctrine()->getRepository(User::class)->find($targetUser));
            $suggestion->setSuggestUser($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestion);
            $em->flush();

            $responce = ['status' => 'success'];

            $serializer = $this->container->get('jms_serializer');
            $json = $serializer->serialize($responce, 'json');
            return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
        }else {
            $serializer = $this->container->get('jms_serializer');
            $json = $serializer->serialize(['status' => 'error'],  'json');
            return new JsonResponse($json);
        }
    }



    /**
     * @Route("/acceptSuggestion", methods={"POST"})
     */
    public function acceptSuggestion(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();
        $csrfToken = $session->get('csrf_token');

        $contentJson = $request->getContent();
        $content = json_decode($contentJson, true);

        $requestedToken = $content['csrf_token'];

        if ( $csrfToken === $requestedToken ) {

            /** @var User $currentUserData */
            $currentUserData = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->getUserWithFriends($this->getUser()->getId());

            /** @var Suggestion $suggestion */
            $suggestion = $this->getDoctrine()
                                ->getRepository(Suggestion::class)
                                ->seeFullSuggestion($content['suggestionId']);

            if ( $suggestion->getAcceptUser()->getId() == $currentUserData->getId() ) {
                $currentUserData->addFriend($suggestion->getSuggestUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($currentUserData);
                $em->flush();

                $this->getDoctrine()
                    ->getRepository(Suggestion::class)
                    ->disableSuggestion($content['suggestionId']);

                return $this->JsonResponce(['status' => 'success']);
            }else {
                return $this->JsonResponce(['status' => 'error']);
            }

        }else {
            $arr = ['status' => 'error'];
            return $this->JsonResponce($arr);
        }
    }


    /**
     * @Route("/getUnseenSuggestion", methods={"GET"})
     */
    public function getUnseenSuggestion()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $countArr = $this->getDoctrine()
                    ->getRepository(Suggestion::class)
                    ->getUnseenSuggestions($this->getUser()->getId());

        $count = $countArr[1];

        if ( $count > 0 ) {
            return $this->JsonResponce(['status' => 'success', 'suggestion' => 'true']);
        }else {
            return $this->JsonResponce(['status' => 'success', 'suggestion' => 'false']);
        }

    }


    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
