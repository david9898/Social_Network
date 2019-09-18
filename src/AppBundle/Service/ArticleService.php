<?php

namespace AppBundle\Service;


use AppBundle\Entity\Article;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ArticleService
{
    private $redis;
    private $doctrine;
    private $articleDirectory;

    public function __construct(EntityManagerInterface $doctrine, RedisClientCreator $redisClientCreator, $articleDirectory)
    {
        $this->redis            = $redisClientCreator->getRedisClient();
        $this->doctrine         = $doctrine;
        $this->articleDirectory = $articleDirectory;
    }

    /**
     * @param Article $article
     * @return Article
     */
    public function addArticleToDatabase(Article $article, FileUploader $fileUploader, $currentUser)
    {
            $file = $article->getImage();

            $fileName = $fileUploader->uploadImage($file, $this->articleDirectory);

            $article->setImage($fileName);
            $article->setAuthor($currentUser);

            $em = $this->doctrine;
            $em->persist($article);
            $em->flush();

            return $article;
    }

    /**
     * @param $userId
     * @param $articleId
     * @return array
     */
    public function deliverArticleToFollowers($userId, $articleId)
    {
        $time      = time();
        $followers = $this->redis->zrange('followers: '. $userId, 0, -1);

        $pipeline = $this->redis->pipeline();

        foreach ($followers as $follower) {
            $pipeline->zadd('home: ' . $follower, [$articleId => $time]);
        }

        $pipeline->zadd('home: ' . $userId, [$articleId => $time]);

        $pipeline->execute();

        return $followers;
    }

    /**
     * @param Article $article
     * @param array $followers
     * @return bool
     */
    public function addArticleToRedis(Article $article, $followers)
    {
        $currentUserData = $this->redis->hmget('user: ' . $article->getAuthor()->getId(), ['fullName', 'profileImage']);

        $this->redis->hmset('article: ' . $article->getId(), [
            'id'           => $article->getId(),
            'authorId'     => $article->getAuthor()->getId(),
            'description'  => $article->getDescription(),
            'likes'        => 0,
            'dateAdded'    => $article->getDateAdded(),
            'articleImage' => $article->getImage(),
            'fullName'     => $currentUserData[0],
            'profileImage' => $currentUserData[1],
            'delivered'    => json_encode($followers),
            'comments'     => 0,
        ]);

        return true;
    }

    /**
     * @param User $user
     * @param integer $articleId
     * @return bool
     */
    public function addLikeToDb(User $user, $articleId)
    {
        $article = $this->doctrine
                        ->getRepository(Article::class)
                        ->find($articleId);

        $article->addLike($user);

        $em = $this->doctrine;
        $em->persist($article);
        $em->flush();

        return true;
    }

    /**
     * @param User $user
     * @param integer $articleId
     * @return bool
     */
    public function addLikeToRedis(User $user, $articleId)
    {
        if ( !$this->redis->hexists('article: ' . $articleId, 'likes') ) {
            return false;
        }

        if ( $this->redis->zrank('likes: ' . $articleId, $user->getId()) !== null ) {
            return false;
        }

        $this->redis->hincrby('article: ' . $articleId, 'likes', 1);

        $this->redis->zadd('likes: ' . $articleId, [$user->getId() => time()]);

        return true;
    }

    /**
     * @param array $articles
     * @param integer $userId
     * @return array
     */
    public function checkArticlesLikes($articles, $userId)
    {
        $pipeline = $this->redis->pipeline();

        foreach ($articles as $article) {
            $pipeline->zrank('likes: ' . $article[5], $userId);
        }

        $isLikes = $pipeline->execute();

        for ($i = 0; $i < count($isLikes); $i++) {
            if ( $isLikes[$i] !== null ) {
                array_push($articles[$i], 1);
            }else {
                array_push($articles[$i], 0);
            }
        }

        return $articles;
    }

    public function getArticleLikes($articleId)
    {
        return $this->redis->zcount('likes: ' . $articleId, PHP_INT_MIN, PHP_INT_MAX);
    }

}