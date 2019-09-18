<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Article;
use AppBundle\Entity\Suggestion;
use AppBundle\Service\ArticleService;
use AppBundle\Service\RedisClientCreator;
use Hoa\Iterator\Limit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{

    /**
     * @Route("/home", name="home")
     */
    public function showArticles(RedisClientCreator $redisClientCreator, ArticleService $articleService)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = new Session();

        $currentId = $this->getUser()->getId();

        if ( $session->get('currentId') === null ) {
            $session->set('currentId', $currentId);
        }

        $csrfToken = bin2hex(random_bytes(32));

        $session->set('csrf_token', $csrfToken);

        $articles = $redisClientCreator
                                    ->getRedisClient()
                                    ->zrevrangebyscore('home: ' . $currentId, PHP_INT_MAX, PHP_INT_MIN, ['LIMIT' => [0, 5]]);

        $pipeline = $redisClientCreator->getRedisClient()
                                        ->pipeline();

        foreach ($articles as $articleId) {
            $pipeline->hmget('article: ' . $articleId, ['profileImage', 'fullName', 'likes', 'description', 'dateAdded', 'id', 'articleImage', 'comments']);
        }

        $home = $pipeline->execute();

        $home = $articleService->checkArticlesLikes($home, $currentId);

        return $this->render('home/showArticles.html.twig', [
            'articles'   => $home,
            'csrf_token' => $csrfToken
        ]);
    }
}
