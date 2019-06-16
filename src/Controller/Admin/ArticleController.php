<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Form\FilterType;
use App\Form\StatusType;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class ArticleController extends AbstractController
{
    private $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @Route("/articles", name="admin_article_index", methods={"GET", "POST"})
     */
    public function index(Request $request, ArticleRepository $articleRepository): Response
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
     * @Route("/article/{id}/status-change", name="admin_article_status-change", methods={"GET", "POST"})
     */
    public function statusChange(Request $request, Article $article): Response
    {
        $form = $this->createForm(StatusType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];
            $this->articleService->setStatus($article, $status);

            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
}
