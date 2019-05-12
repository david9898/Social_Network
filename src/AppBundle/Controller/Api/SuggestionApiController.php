<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use AppBundle\Service\SuggestionService;
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
    private $suggestionService;

    public function __construct(SuggestionService $service)
    {
        $this->suggestionService = $service;
    }

    /**
     * @Route("/addSuggestion")
     * @Method("POST")
     */
    public function addSuggest(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session   = new Session();
        $content   = $request->getContent();
        $realData  = json_decode($content, true);
        $csrfToken = $session->get('csrf_token');
        $currentId = $session->get('currentId');
        $friends   = $session->get('friends');


        $responce = $this->suggestionService
                            ->validateSuggestion($currentId, $realData, $csrfToken, $friends);

        return $this->JsonResponce($responce);
    }



    /**
     * @Route("/acceptSuggestion", methods={"POST"})
     */
    public function acceptSuggestion(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session     = new Session();
        $csrfToken   = $session->get('csrf_token');
        $contentJson = $request->getContent();
        $friends     = $session->get('friends');
        $content     = json_decode($contentJson, true);
        $currentId   = $this->getUser()->getId();

        $responce = $this->suggestionService
                            ->acceptSuggestion($currentId, $content, $csrfToken, $friends);

        if ( $responce['status'] === 'success' ) {
            $friends[] = $responce['newFriend'];

            $session->set('friends', $friends);
        }

        return $this->JsonResponce($responce);
    }


    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
