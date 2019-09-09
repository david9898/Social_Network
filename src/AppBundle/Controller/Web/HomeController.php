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

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrf_token', $csrfToken);

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticles(0);

        return $this->render('home/showArticles.html.twig', [
            'articles'   => $articles,
            'csrf_token' => $csrfToken
        ]);
    }
}
