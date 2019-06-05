<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public const ORDER_TYPES = ['ASC', 'DESC'];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function searchByEmail(?string $query, int $page = 1, ?string $key = null, ?string $type = null): Paginator
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%');
        
        if ($key && in_array($type, self::ORDER_TYPES)) {
            $qb->orderBy('u.' . $key, $type);
        }

        return $this->paginate($qb->getQuery(), $page ?: 1);
    }

    public function paginate($dql, int $page = 1, int $limit = User::COUNT_ON_PAGE): Paginator
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
            ->orderBy('u.id', 'ASC')
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
