<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{

    /**
     * @Route("/home", name="home")
     */
    public function showArticles()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->getArticles(0);

        return $this->render('home/showArticles.html.twig', [
            'articles' => $articles,
        ]);
    }
}
