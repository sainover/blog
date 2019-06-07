<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private $tokenGeneratorService;
    private $emailsService;
    private $passwordEncoder;
    private $manager;

    public function __construct(
        TokenGeneratorService $tokenGeneratorService,
        EmailsService $emailsService,
        UserPasswordEncoderInterface $passwordEncoder,
        ObjectManager $manager
    ) {
        $this->tokenGeneratorService = $tokenGeneratorService;
        $this->emailsService = $emailsService;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
    }

    public function register(User $user): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            )
        );
        $user->eraseCredentials();
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
}
