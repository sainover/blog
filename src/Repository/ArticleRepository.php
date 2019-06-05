<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findLatest(int $page = 1, 
        Tag $tag = null, 
        array $statuses = null, 
        string $email = null, 
        $dateFrom = null, 
        $dateTo = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('a', 't')
            ->innerJoin('p.author', 'a')
            ->leftJoin('p.tags', 't')
            ->orderBy('p.publishedAt', 'DESC')
        ;

        if ($tag) {
            $qb->andWhere(':tag MEMBER OF p.tags')
                ->setParameter('tag', $tag);
        }

        if ($statuses) {
            $qb->andWhere('p.status IN (:statuses)')
            ->setParameter('statuses', $statuses);
        }

        if ($email) {
            $qb->andWhere('a.email LIKE :email')
            ->setParameter(':email', '%' . $email . '%');
        }

        if ($dateFrom) {
            $qb
                ->andWhere('p.publishedAt > :publishedDateFrom')
                ->setParameter(':publishedDateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb
                ->andWhere('p.publishedAt < :publishedDateTo')
                ->setParameter(':publishedDateTo', $dateTo);
        }

        return $this->paginate($qb->getQuery(), $page ?: 1);
    }

    public function paginate($dql, int $page = 1, int $limit = Article::COUNT_ON_PAGE): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);
        return $paginator;
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
