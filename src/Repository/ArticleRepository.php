<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
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

    public function findTop(int $limit = Article::COUNT_TOP): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Tag[] Returns an array of Tag objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findForHomePage($filter): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'author')->addSelect('author')
            ->leftJoin('a.tags', 't')->addSelect('t')
            ->leftJoin('a.comments', 'c')->addSelect('c')
            ->where('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
            ->orderBy('a.publishedAt', 'DESC')
        ;

        if (null !== $filter['tag']) {
            $qb->andWhere(':tag MEMBER OF a.tags')->setParameter('tag', $filter['tag']);
        }

        return $this->paginate($qb->getQuery(), $filter['page'], Article::COUNT_ON_PAGE);
    }

    public function findForArticlePage(int $id): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.id = :id')->setParameter('id', $id)
            ->andWhere('a.status = :status')->setParameter('status', Article::STATUS_PUBLISHED)
            ->leftJoin('a.author', 'author')->addSelect('author')
            ->leftJoin('a.tags', 't')->addSelect('t')
            ->leftJoin('a.comments', 'c')->addSelect('c')
            ->leftJoin('c.author', 'ca')->addSelect('ca')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findForAdminPage($filter): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'author')->addSelect('author')
        ;

        if (isset($filter['status']) && in_array($filter['status'], Article::STATUSES_VIEWABLE_TO_ADMIN)) {
            $statuses = $filter['status'];
        } else {
            $statuses = array_values(Article::STATUSES_VIEWABLE_TO_ADMIN);
        }
        $qb->andWhere('a.status IN (:statuses)')->setParameter('statuses', $statuses);

        if (isset($filter['email'])) {
            $qb->andWhere('author.email LIKE :query')->setParameter('query', '%'.$filter['email'].'%');
        }

        if (isset($filter['dateFrom'])) {
            $qb->andWhere('a.publishedAt > :dateFrom')->setParameter('dateFrom', $filter['dateFrom']);
        }

        if (isset($filter['dateTo'])) {
            $qb->andWhere('a.publishedAd < :dateTo')->setParameter('dateTo', $filter['dateTo']);
        }

        return $this->paginate($qb->getQuery(), $filter['page'], Article::COUNT_ON_PAGE);
    }

    public function findByUser(int $page, User $user): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.author = :val')
            ->setParameter('val', $user)
        ;

        return $this->paginate($qb->getQuery(), $page, Article::COUNT_ON_PAGE);
    }

    public function paginate($dql, int $page, int $limit = Article::COUNT_ON_PAGE): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit)
        ;

        return $paginator;
    }
}
