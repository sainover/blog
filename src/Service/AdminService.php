<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;

class AdminService
{
    private $manager;
    private $userRepository;

    public function __construct(
        ObjectManager $manager,
        UserRepository $userRepository
    ) {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
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
        $users = $this->userRepository
            ->searchByEmail($query);

        foreach ($users as $user) {
            $data[] = $user->getEmail();
        }

        return $data;
    }
}
