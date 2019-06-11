<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\StatusType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Service\AdminService;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $articleService;
    private $adminService;

    public function __construct(ArticleService $articleService, AdminService $adminService)
    {
        $this->articleService = $articleService;
        $this->adminService = $adminService;
    }

    /**
     * @Route("/articles", name="admin_articles", methods={"GET", "POST"})
     */
    public function adminArticles(Request $request, ArticleRepository $articleRepository)
    {
        $filter = [
            'page' => $request->query->getInt('page', 1),
            'status' => null,
            'email' => null,
            'dateFrom' => null,
            'dateTo' => null,
        ];

        $form = $this->createForm(FilterType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $filter['status'] = $form->getData()['status'];
            $filter['email'] = $form->getData()['email'];
            $filter['dateFrom'] = $form->getData()['dateFrom'];
            $filter['dateTo'] = $form->getData()['dateTo'];
        }

        $articles = $articleRepository->findForAdminpe($filter);

        $maxPages = ceil(count($articles) / Article::COUNT_ON_PAGE);

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
    public function adminArticleChangeStatus(Request $request, Article $article)
    {
        $form = $this->createForm(StatusType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];
            $this->articleService->setArticleStatus($article, $status);

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
    public function adminUsers(Request $request, UserRepository $userRepository)
    {
        $page = $request->query->getInt('page', 1);
        $filter = [
            'email' => $request->get('query'),
            'orderBy' => $request->get('sortBy'),
            'orderType' => $request->get('sortType'),
        ];

        $users = $userRepository->findForAdminpe($page, $filter);
        $maxPages = ceil(count($users) / User::COUNT_ON_PAGE);

        return $this->render('admin/user/index.html.twig', [
            'thisPage' => $page,
            'maxPages' => $maxPages,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/users/emails", name="admin_users_emails")
     */
    public function adminUsersEmails(Request $request): JsonResponse
    {
        $data = $this->adminService->searchUsersEmailsAsArray($request->get('query'));

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/user/{id}/block", name="admin_user_block", methods={"GET", "POST"})
     */
    public function adminUserBlock(User $user): Response
    {
        $this->adminService->blockUser($user);

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/user/{id}/activate", name="admin_user_activate", methods={"GET", "POST"})
     */
    public function adminUserActivate(User $user): Response
    {
        $this->adminService->activateUser($user);

        return $this->redirectToRoute('admin_users');
    }
}
