<?php

namespace AppBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{

    public function getArticles(int $scrollPoint)
    {
        $firstArticle = $scrollPoint * 15;
        $em = $this->getEntityManager();
        $qb = $em->createQuery('SELECT a, u FROM AppBundle:Article a JOIN a.author u')
                    ->setFirstResult($firstArticle)
                    ->setMaxResults(15);

        return $qb->getResult();
    }

    public function getArticlesOnUser($authorId)
    {
        $dql = 'SELECT a.id, a.description, a.dateAdded, a.image FROM AppBundle:Article a WHERE a.author = :authorId';

        $query = $this->getEntityManager()
                    ->createQuery($dql)
                    ->setParameter('authorId', $authorId)
                    ->setMaxResults(15);

        return $query->getResult();
    }
}
