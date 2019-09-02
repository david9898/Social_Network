<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Suggestion;
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

        $currentId = $this->getUser()->getId();

        $session   = new Session();
        $csrfToken = $session->get('csrf_token');

        $content   = $request->getContent();
        $realData  = json_decode($content, true);

        $responce = $this->suggestionService->addSuggestion($realData['csrf_token'], $csrfToken, $currentId, $realData['target_user']);

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
        $content     = json_decode($contentJson, true);

        $currentId   = $this->getUser()->getId();

        $responce = $this->suggestionService
                            ->acceptSuggestion($content['csrf_token'], $csrfToken, $currentId, $content['userId']);

        return $this->JsonResponce($responce);
    }

    /**
     * @Route("/disableSuggestion/{userId}/{csrfToken}", methods={"GET"})
     */
    public function disableSuggestion($userId, $csrfToken)
    {
        $session       = new Session();

        $realCsrfToken = $session->get('csrf_token');
        $myId          = $this->getUser()->getId();

        $responce      = $this->suggestionService->denySuggestion($csrfToken, $realCsrfToken, $myId, $userId);

        return $this->JsonResponce($responce);
    }

    /**
     * @Route("/removeYourSuggestion/{userId}/{csrfToken}", methods={"GET"})
     */
    public function removeYourSuggestion($userId, $csrfToken)
    {
        $session       = new Session();

        $realCsrfToken = $session->get('csrf_token');
        $myId          = $this->getUser()->getId();

        $responce      = $this->suggestionService->removeYourSuggestion($csrfToken, $realCsrfToken, $myId, $userId);

        return $this->JsonResponce($responce);
    }

    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
