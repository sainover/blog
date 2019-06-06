<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Article;

class AdminService
{
    private $manager;

    public function __construct( 
        ObjectManager $manager
    ) {
        $this->manager = $manager;
    }

    public function blockUser(User $user)
    {
        $user->setStatus(User::STATUS_BLOCKED);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function activateUser(User $user)
    {
        $user->setStatus(User::STATUS_ACTIVE);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function searchUsersEmailsAsArray($query): array
    {
        $data = [];
        $users = $this->manager
            ->getRepository(User::class)
            ->customFind(null, [], ['email' => $query], []);
            
        foreach($users as $user) {
            $data[] = $user->getEmail();
        }
        return $data;
    }
}