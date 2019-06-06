<?php

namespace App\Service;

use App\Service\TokenGeneratorService;
use App\Service\EmailsService;
use App\Entity\User;
use App\Security\UserAuthenticator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserService
{
    private $tokenGeneratorService;
    private $emailsService;
    private $passwordEncoder;
    private $guardHandler;
    private $authenticator;
    private $manager;

    public function __construct(TokenGeneratorService $tokenGeneratorService, 
        EmailsService $emailsService,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        UserAuthenticator $authenticator,
        ObjectManager $manager
    ) {
        $this->tokenGeneratorService = $tokenGeneratorService;
        $this->emailsService = $emailsService;
        $this->passwordEncoder = $passwordEncoder;
        $this->guardHandler = $guardHandler;
        $this->authenticator = $authenticator;
        $this->manager = $manager;
    }

    public function register(User $user, String $plainPassword): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $plainPassword
            )
        );
        $user->setToken($this->tokenGeneratorService->generate());
        $user->setStatus(User::STATUS_NOT_VERIFIED);

        $this->emailsService->emailConfirmation($user->getFullName(), $user->getEmail(), $user->getToken());

        $this->manager->persist($user);
        $this->manager->flush();
    }

    public function confirm(String $token): void
    {
        $user = $this->manager
            ->getRepository(User::class)
            ->findOneBy(['token' => $token])
        ;
        $user->setToken('');
        $user->setStatus(User::STATUS_ACTIVE);
        $this->manager->flush();
    }
}