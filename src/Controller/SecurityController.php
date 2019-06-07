<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // cotroller can be blank: it will never be execute
    }

    /**
     * @Route("/confirmation/{token}", name="confirmation", methods={"GET"})
     */
    public function confirmation(UserRepository $userRepository, string $token, UserService $userService): Response
    {
        $user = $userRepository->findOneByToken($token);

        if (!$user) {
            throw $this->createNotFoundException('Confirmation link not valid');
        } else {
            $userService->confirm($user);
            $this->addFlash('notice', 'Account succesfully activated');

            return $this->redirectToRoute('app_login');
        }
    }
}
