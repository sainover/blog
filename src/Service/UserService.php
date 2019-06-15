<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;

class UserService
{
    private $tokenGeneratorService;
    private $emailsService;
    private $passwordEncoder;
    private $manager;
    private $userRepository;

    public function __construct(
        TokenGeneratorService $tokenGeneratorService,
        EmailsService $emailsService,
        UserPasswordEncoderInterface $passwordEncoder,
        ObjectManager $manager,
        UserRepository $userRepository
    ) {
        $this->tokenGeneratorService = $tokenGeneratorService;
        $this->emailsService = $emailsService;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }

    public function register(User $user): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            )
        );
        $user->setToken($this->tokenGeneratorService->generate());
        $user->setStatus(User::STATUS_NOT_VERIFIED);

        $this->manager->persist($user);
        $this->manager->flush();

        $this->emailsService->sendEmailConfirmation($user);
    }

    public function confirm(User $user): void
    {
        $user->setToken(null);
        $user->setStatus(User::STATUS_ACTIVE);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function block(User $user): void
    {
        $user->setStatus(User::STATUS_BLOCKED);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function activate(User $user): void
    {
        $user->setStatus(User::STATUS_ACTIVE);
        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function searchEmailsAsArray($query): array
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
