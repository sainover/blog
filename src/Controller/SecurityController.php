<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\PasswordResetType;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

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
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        // cotroller can be blank: it will never be execute
    }

    /**
     * @Route("/confirmation/{token}", name="confirmation")
     */
    public function confirmation(string $token, UserRepository $userRepository): Response
    {
        $user = $userRepository->findByToken($token);
        if (!$user) {
            throw $this->createNotFoundException('Confirmation link not valid');
        }

        $this->userService->confirm($user);
        $this->addFlash('notice', 'Account succesfully activated');

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/password-forgot", name="security_password-forgot", methods={"GET", "POST"})
     */
    public function passwordForgot(Request $request, UserRepository $userRepository)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('Recover', SubmitType::class)
            ->getForm()
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findByLogin($form->getData()['email']);
            if (!$user) {
                throw $this->createNotFoundException('User with this email not found');
            }

            $this->userService->passwordForgot($user);
            $this->addFlash('notice', 'We sent on your email link to reset account password.');
        }

        return $this->render('user/password-forgot.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/password-reset/{token}", name="security_password-reset", methods={"GET", "POST"})
     */
    public function passwordReset(string $token, Request $request, UserRepository $userRepository)
    {
        $user = $userRepository->findByToken($token);
        if (!$user) {
            throw $this->createNotFoundException('Confirmation link not valid');
        }

        $form = $this->createForm(PasswordResetType::class, $user);
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->userService->passwordReset($user);

            $this->addFlash('notice', 'New Password saved');
        }

        return $this->render('user/password-reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
