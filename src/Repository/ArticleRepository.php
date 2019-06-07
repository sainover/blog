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
    public const ORDER_TYPES = ['ASC', 'DESC'];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function customFind(int $page = 1, ?array $options = [], ?array $searches = [], ?array $orders = []): Paginator
    {
        $qb = $this->createQueryBuilder('a');

        $qb = $this->addOptions($qb, $options);
        $qb = $this->addSearches($qb, $searches);
        $qb = $this->addOrders($qb, $orders);

        return $this->paginate($qb->getQuery(), $page ?: 1);
    }

    private function addOrders($query, array $orders)
    {
        foreach ($orders as $field => $type) {
            if ($field && in_array($type, self::ORDER_TYPES)) {
                $query->orderBy('a.'.$field, $type);
            }
        }

        return $query;
    }

    private function addSearches($query, array $searches)
    {
        foreach ($searches as $field => $value) {
            if (!$value) {
                continue;
            }

            switch ($field) {
                case 'email':
                    $query
                        ->innerJoin('a.author', 'u')
                        ->andWhere('u.email LIKE :value')
                        ->setParameter('value', '%'.$value.'%')
                    ;
                    break;
                default:
                    $query
                        ->andWhere('a.'.$field.' LIKE :value')
                        ->setParameter('value', '%'.$value.'%')
                    ;
            }
        }

        return $query;
    }

    private function addOptions($query, array $options)
    {
        foreach ($options as $field => $value) {
            if (!$value) {
                continue;
            }

            switch ($field) {
                case 'tag':
                    $query->andWhere(':tag MEMBER OF a.tags')
                        ->setParameter('tag', $value)
                    ;
                    break;
                case 'statuses':
                    $query->andWhere('a.status IN (:statuses)')
                        ->setParameter('statuses', $value)
                    ;
                    break;
                case 'dateFrom':
                    $query->andWhere('a.publishedAt > :dateFrom')
                        ->setParameter('dateFrom', $value)
                    ;
                    break;
                case 'dateTo':
                    $query->andWhere('a.publishedAt < :dateTo')
                        ->setParameter('dateTo', $value)
                    ;
                    break;
                default:
                    $query->andWhere('a.'.$field.' = :value')
                        ->setParameter('value', $value)
                    ;
            }
        }

        return $query;
    }

    public function paginate($dql, int $page = 1, int $limit = User::COUNT_ON_PAGE): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit)
        ;

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
