<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class SuggestionController extends Controller
{

    /**
     * @Route("/suggestions", name="suggestions_my_suggestions")
     */
    public function getMySuggestions()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrf_token', $csrfToken);

        $currentId = $this->getUser()->getId();

        $suggestions = $this->getDoctrine()
            ->getRepository(Suggestion::class)
            ->seeAllSuggestions($currentId);

        return $this->render('users/mySuggestions.html.twig', [
            'suggestions' => $suggestions,
            'csrfToken' => $csrfToken,
        ]);
    }


    /**
     * @Route("/findFriends", name="user_friends")
     */
    public function findFriends()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrf_token', $csrfToken);

        $currentId = $this->getUser()->getId();

        /** @var User $currentUser */
        $currentUser = $this->getDoctrine()
                            ->getRepository(User::class)
                            ->getFullUser($currentId);

        $suggestions = $this->getDoctrine()
                            ->getRepository(Suggestion::class)
                            ->getMySendSuggestions($currentId);

        $friends = $currentUser->getMyFriends();

        $friendsWithMe = $currentUser->getFriendsWithMe();

        $friendsId = [];

        $friendsId[] = $this->getUser()->getId();

        foreach ($friends as $friend) {
            $friendsId[] = $friend->getId();
        }

        foreach ($friendsWithMe as $friend) {
            $friendsId[] = $friend->getId();
        }

        foreach ($suggestions as $suggestion) {
            if ( $suggestion['suggestUser'] === $currentId ) {
                $friendsId[] = $suggestion['acceptUser'];
            }else {
                $friendsId[] = $suggestion['suggestUser'];
            }
        }

        $users = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->findUsersBySearch(1, $friendsId);


        return $this->render('users/findFriends.html.twig', [
            'friends' => $users,
            'csrfToken' => $csrfToken,
        ]);
    }
}