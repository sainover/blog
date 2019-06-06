<?php

namespace App\Controller;

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
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
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
    public function confirmation(String $token, UserService $userService)
    {
        if ($userService->confirm($token)) {
            $this->addFlash('notice', 'Аккаунт успешно активирован');
            return $this->redirectToRoute('app_login');
        } else {
            throw $this->createNotFoundException('Страница не найдена');
        }
    }
}
