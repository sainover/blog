<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

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

        foreach ($users as $user) {
            $data[] = $user->getEmail();
        }

        return $data;
    }
}
