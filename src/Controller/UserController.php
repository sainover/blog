<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/article")
 */
class UserController extends AbstractController
{
    private $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * @Route("/", name="user_article_index")
     */
    public function userArticleIndex(Request $request, ArticleRepository $articleRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $user = $this->getUser();

        $userArticles = $articleRepository->findByUser($page, $user);
        $maxPages = ceil(count($userArticles) / Article::COUNT_ON_PAGE);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'maxPages' => $maxPages,
            'thisPage' => $page,
            'articles' => $userArticles,
        ]);
    }

    /**
     * @Route("/new", name="user_article_new", methods={"GET", "POST"})
     */
    public function userArticleNew(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->articleService->createArticle($article);

            return $this->redirectToRoute('user_article_index');
        }

        return $this->render('user/article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_article_edit", methods={"GET", "POST"})
     */
    public function userArticleEdit(ObjectManager $manager, Article $article, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('user_article_index');
        }

        return $this->render('user/article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_article_delete", methods={"DELETE"})
     */
    public function userArticleDelete(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::DELETE, $article);

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $this->articleService->deleteArticle($article);

            return $this->redirectToRoute('user_article_index');
        }
    }

    /**
     * @Route("/{id}/publish", name="publish", methods={"GET", "POST"})
     */
    public function userArticleSendToModeration(Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $this->articleService->sendToModeration($article);

        return $this->redirectToRoute('user_article_index');
    }
}
