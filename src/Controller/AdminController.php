<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\StatusType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserService;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $articleService;
    private $userService;

    public function __construct(ArticleService $articleService, UserService $userService)
    {
        $this->articleService = $articleService;
        $this->userService = $userService;
    }

    /**
     * @Route("/articles", name="admin_articles", methods={"GET", "POST"})
     */
    public function articles(Request $request, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(FilterType::class);
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $filter = $form->getData();
        }

        $filter['page'] = $request->query->getInt('page', 1);

        $articles = $articleRepository->findForAdminPage($filter);
        $maxPages = ceil(count($articles) / $articles->getQuery()->getMaxResults());

        return $this->render('admin/article/index.html.twig', [
            'form' => $form->createView(),
            'thisPage' => $filter['page'],
            'maxPages' => $maxPages,
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}/edit", name="admin_article_edit", methods={"GET", "POST"})
     */
    public function articleChangeStatus(Request $request, Article $article): Response
    {
        $form = $this->createForm(StatusType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];
            $this->articleService->setStatus($article, $status);

            return $this->redirectToRoute('admin_articles');
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function users(Request $request, UserRepository $userRepository): Response
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
     * @Route("/users/emails", name="admin_users_emails")
     */
    public function usersEmails(Request $request): JsonResponse
    {
        $data = $this->userService->searchEmailsAsArray($request->get('query'));

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/user/{id}/block", name="admin_block_user", methods={"POST"})
     */
    public function blockUser(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('block_user'.$user->getId(), $request->request->get('_token'))) {
            $this->userService->block($user);
        }

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user/{id}/activate", name="admin_activate_user", methods={"POST"})
     */
    public function activateUser(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('activate_user'.$user->getId(), $request->request->get('_token'))) {
            $this->userService->activate($user);
        }

        return $this->redirectToRoute('admin_users');
    }
}
