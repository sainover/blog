<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class UserController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/users", name="admin_user_index")
     */
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $filter = [
            'email' => $request->get('query'),
            'orderBy' => $request->get('sortBy'),
            'orderType' => $request->get('sortType'),
        ];

        $users = $userRepository->findForAdminPage($page, $filter);
        $maxPages = ceil(count($users) / $users->getQuery()->getMaxResults());

        return $this->render('admin/user/index.html.twig', [
            'thisPage' => $page,
            'maxPages' => $maxPages,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/emails", name="admin_user_emails")
     */
    public function emails(Request $request): JsonResponse
    {
        $data = $this->userService->searchEmailsAsArray($request->get('query'));

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/user/{id}/block", name="admin_user_block", methods={"POST"})
     */
    public function block(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('user_block'.$user->getId(), $request->request->get('_token'))) {
            $this->userService->block($user);
        }

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * @Route("/user/{id}/activate", name="admin_user_activate", methods={"POST"})
     */
    public function activate(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('user_activate'.$user->getId(), $request->request->get('_token'))) {
            $this->userService->activate($user);
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
