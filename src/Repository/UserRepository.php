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
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByToken(string $token): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.token = :val')->setParameter('val', $token)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByLogin(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')->setParameter('email', $email)
            ->andWhere('u.status = :status')->setParameter('status', User::STATUS_ACTIVE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findForAdminpe(int $page, array $filter): Paginator
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :query')->setParameter('query', '%'.$filter['email'].'%')
        ;

        if (null !== $filter['orderBy'] && in_array($filter['orderType'], ['ASC', 'DESC'])) {
            $qb->orderBy('u.'.$filter['orderBy'], $filter['orderType']);
        }

        return $this->paginate($qb->getQuery(), $page, User::COUNT_ON_PAGE);
    }

    public function searchByEmail($query): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :query')->setParameter('query', '%'.$query.'%')
            ->orderBy('u.email', 'ASC')
            ->setMaxResults(User::COUNT_ON_PAGE)
            ->getQuery()
            ->getResult()
        ;
    }

    public function paginate($dql, $page = 1, int $limit = User::COUNT_ON_PAGE): Paginator
    {
        $paginator = new Paginator($dql);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
