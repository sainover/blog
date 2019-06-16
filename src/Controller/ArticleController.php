<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Regard;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleService;
use App\Service\CommentService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    private $articleService;
    private $commentService;

    public function __construct(ArticleService $articleService, CommentService $commentService)
    {
        $this->articleService = $articleService;
        $this->commentService = $commentService;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        TagRepository $tagRepository,
        UserRepository $userRepository
    ): Response {
        $articleFilter = [
            'page' => $request->query->getInt('page', 1),
            'tag' => $tagRepository->findByName($request->query->get('tag')),
        ];

        $articles = $articleRepository->findForHomePage($articleFilter);
        $maxPages = ceil(count($articles) / $articles->getQuery()->getMaxResults());

        return $this->render('article/index.html.twig', [
            'thisPage' => $articleFilter['page'],
            'maxPages' => $maxPages,
            'articles' => $articles,
            'topArticles' => $articleRepository->findTop(),
            'topUsers' => $userRepository->findTop(),
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show", methods={"GET"})
     */
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findForArticlePage($id);

        $form = $this->createForm(CommentType::class, null, [
            'action' => $this->generateUrl('article_comment', ['id' => $article->getId()]),
        ]);

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("article/{id}/comment", name="article_comment", methods={"POST"})
     */
    public function addComment(Request $request, Article $article): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commentService->create($comment, $article);

            $this->addFlash(
                'notice',
                sprintf('You added commentary to article %s', $article->getTitle())
            );
        }

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    /**
     * @Route("/new", name="user_article_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->articleService->create($article);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET", "POST"})
     */
    public function edit(ObjectManager $manager, Article $article, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        $form = $this->createForm(ArticleType::class, $article);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $manager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_remove", methods={"DELETE"})
     */
    public function remove(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::DELETE, $article);

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $this->articleService->remove($article);
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/{id}/send-to-moderation", name="article_send-to-moderation", methods={"POST"})
     */
    public function sendToModeration(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::EDIT, $article);

        if ($this->isCsrfTokenValid('send-to-moderation'.$article->getId(), $request->request->get('_token'))) {
            $this->articleService->sendToModeration($article);
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/article/{id}/regard/like", name="article_like", methods={"POST"})
     */
    public function like(Article $article): JsonResponse
    {
        $ratingValue = $this->articleService->toggleRegard($article, Regard::LIKE);

        return new JsonResponse($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/regard/dislike", name="article_dislike", methods={"POST"})
     */
    public function dislike(Article $article): JsonResponse
    {
        $ratingValue = $this->articleService->toggleRegard($article, Regard::DISLIKE);

        return new JsonResponse($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/tags", name="article_tags")
     */
    public function tags(Article $article): JsonResponse
    {
        return new JsonResponse($this->articleService->getTags($article), Response::HTTP_OK);
    }
}
