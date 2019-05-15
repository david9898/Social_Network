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
                ->find($currentId);

            $suggestions = $this->getDoctrine()
                                ->getRepository(Suggestion::class)
                                ->getSendAndAcceptSuggestions($currentId);

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

            $session->set('friends', $friendsId);
        }

        if ( $session->get('suggestionsFromMe') === null ) {
            $suggestionsFromMe = $this->getDoctrine()
                                        ->getRepository(Suggestion::class)
                                        ->getSendFromMeSuggestions($currentId);

            $suggestion = [];

            foreach ($suggestionsFromMe as $item) {
                $suggestion[] = $item['acceptUser'];
            }

            $session->set('suggestionsFromMe', $suggestion);
        }

        if ( $session->get('suggestionsToMe') === null ) {
            $suggestionsToMe = $this->getDoctrine()
                                    ->getRepository(Suggestion::class)
                                    ->getSuggestionsToMe($currentId);

            $suggestion = [];

            foreach ($suggestionsToMe as $item) {
                $suggestion[] = $item['sendUser'];
            }

            $session->set('suggestionsToMe', $suggestion);
        }

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticles(0);

        return $this->render('home/showArticles.html.twig', [
            'articles' => $articles,
        ]);
    }
}
