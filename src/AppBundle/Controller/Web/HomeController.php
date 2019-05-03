<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Article;
use AppBundle\Entity\Suggestion;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{

    /**
     * @Route("/home", name="home")
     */
    public function showArticles()
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();
        $currentId = $this->getUser()->getId();

        if ( $session->get('currentId') === null ) {
            $session->set('currentId', $currentId);
        }

        if ( $session->get('friends') === null ) {
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
                if ($suggestion['suggestUser'] === $currentId) {
                    $friendsId[] = $suggestion['acceptUser'];
                } else {
                    $friendsId[] = $suggestion['suggestUser'];
                }
            }

            $session->set('friends', $friendsId);
        }

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticles(0);

        return $this->render('home/showArticles.html.twig', [
            'articles' => $articles,
        ]);
    }
}
