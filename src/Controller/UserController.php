<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Regard;
use App\Service\ArticleService;
use App\Repository\TagRepository;
use App\Security\Voter\ArticleVoter;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @Route("/", name="user_article_index", methods={"GET"})
     */
    public function userArticleIndex()
    {
        $user = $this->getUser();
        $authorArticles = $this->articleService->getAuthorArticles($user);

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'articles' => $authorArticles
        ]);
    }

    /**
     * @Route("/new", name="user_article_new", methods={"GET", "POST"})
     */
    public function userArticleNew(Request $request)
    {
        $article = new Article;
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->articleService->createArticle($article);
            return $this->redirectToRoute('user_article_index');
        }

        return $this->render('user/article/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_article_edit", methods={"GET", "POST"})
     */
    public function userArticleEdit(Article $article, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->articleService->updateArticle($article);
            return $this->redirectToRoute('user_article_index');
        }

        return $this->render('user/article/edit.html.twig', [
            'form'=> $form->createView()
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
            return $this->redirectToRoute("user_article_index");
        }
    }

    /**
     * @Route("/{id}/publish", name="publish", methods={"GET", "POST"})
     */
    public function userArticleSendToModeration(Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $this->articleService->sendToModeration($article);
        return $this->redirectToRoute("user_article_index");
    }
}
