<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orders = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orders = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public const ORDER_TYPES = ['ASC', 'DESC'];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByToken(?string $token): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token = :val')
            ->setParameter('val', $token)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function customFind($page = 1, ?array $options = [], ?array $searches = [], ?array $orders = []): Paginator
    {
        $qb = $this->createQueryBuilder('u');

        $qb = $this->addOptions($qb, $options);
        $qb = $this->addOrders($qb, $orders);
        $qb = $this->addSearchBy($qb, $searches);

        return $this->paginate($qb->getQuery(), $page ?: 1);
    }

    private function addOrders($query, array $orders)
    {
        foreach ($orders as $field => $type) {
            if (!$type) {
                continue;
            }

            if ($field && in_array($type, self::ORDER_TYPES)) {
                $query->orderBy('u.'.$field, $type);
            }
        }

        return $query;
    }

    private function addSearchBy($query, array $searches)
    {
        foreach ($searches as $field => $value) {
            if (!$value) {
                continue;
            }

            $query->andWhere('u.'.$field.' LIKE :value')
                ->setParameter(':value', '%'.$value.'%')
            ;
        }

        return $query;
    }

    private function addOptions($query, array $options)
    {
        foreach ($options as $field => $value) {
            $query->andWhere('u.'.$field.' = :value')
                ->setParameter(':value', $value)
            ;
        }

        return $query;
    }

    public function paginate($dql, $page = 1, int $limit = User::COUNT_ON_PAGE): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orders('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
