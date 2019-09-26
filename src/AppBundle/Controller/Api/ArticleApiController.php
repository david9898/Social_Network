<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Article;
use AppBundle\Service\ArticleService;
use AppBundle\Service\RedisClientCreator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ArticleApiController extends Controller
{

    /**
     * @Route("/deleteArticle/{id}")
     */
    public function deleteArticle($id)
    {

    }

    /**
     * @Route("/getMoreArticles/{csrfToken}/{firstRes}")
     */
    public function getMoreArticles($csrfToken, $firstRes, RedisClientCreator $redisClientCreator, ArticleService $articleService)
    {
        $session = new Session();

        if ( $session->get('csrf_token') !== $csrfToken ) {
            return [
                'status'      => 'error',
                'description' => 'Wrong token!'
            ];
        }

        $firstResult = $firstRes * 5;
        $currentId   = $this->getUser()->getId();

        $articles = $redisClientCreator
                            ->getRedisClient()
                            ->zrevrangebyscore('home: ' . $currentId, PHP_INT_MAX, PHP_INT_MIN, ['LIMIT' => [$firstResult, 5]]);

        $pipeline = $redisClientCreator->getRedisClient()
            ->pipeline();

        foreach ($articles as $articleId) {
            $pipeline->hmget('article: ' . $articleId, ['profileImage', 'fullName', 'likes', 'description', 'dateAdded', 'id', 'articleImage', 'comments']);
        }

        $home = $pipeline->execute();

        $home = $articleService->checkArticlesLikes($home, $currentId);

        $res = [
            'status' => 'success',
            'articles' => $home
        ];

        return $this->JsonResponce($res);
    }

    /**
     * @Route("/addLike", methods={"POST"})
     */
    public function addLike(Request $request, ArticleService $articleService)
    {
        $contentJson = $request->getContent();
        $content     = json_decode($contentJson, true);
        $session     = new Session();
        $csrfToken   = $content['csrf_token'];
        $currentUser = $this->getUser();
        $articleId   = $content['articleId'];

        if ( $session->get('csrf_token') !== $csrfToken ) {
            return [
                'status'      => 'error',
                'description' => 'Wrong Token!'
            ];
        }

        $articleService->addLikeToDb($currentUser, $articleId);
        $articleService->addLikeToRedis($currentUser, $articleId);
        $likes = $articleService->getArticleLikes($articleId);

        return $this->JsonResponce([
            'status'    => 'success',
            'articleId' => $content['articleId'],
            'likes'     => $likes
        ]);
    }

    /**
     * @Route("/getArticleLikes/{articleId}/{csrfToken}")
     */
    public function getArticleLIkes($articleId, $csrfToken, ArticleService $articleService)
    {
        $session = new Session();

        if ( $session->get('csrf_token') === $csrfToken ) {
            $responce = $articleService->seeWhoIsLiked($articleId);

            return $this->JsonResponce($responce);
        }else {
            return [
                'status'      => 'error',
                'description' => 'Wrong token!'
            ];
        }
    }

    private function JsonResponce($array)
    {
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($array, 'json');
        return new JsonResponse($json, Response::HTTP_OK, array('content-type' => 'application/json'));
    }
}
