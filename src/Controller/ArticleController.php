<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Regard;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Security\Voter\ArticleVoter;
use App\Service\ArticleService;
use App\Service\CommentService;
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
    public function index(Request $request, ArticleRepository $articleRepository): Response
    {
        $articleFilter = [
            'page' => $request->query->getInt('page', 1),
            'tag' => $request->query->get('tag'),
        ];

        $articles = $articleRepository->findForHomepe($articleFilter);

        $maxPages = ceil(count($articles) / Article::COUNT_ON_PAGE);

        return $this->render('index/index.html.twig', [
            'thisPage' => $articleFilter['page'],
            'maxPages' => $maxPages,
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show", methods={"GET", "POST"})
     */
    public function articleShow(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::SHOW, $article);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(ArticleVoter::COMMENT, $article);

            $this->commentService->createComment($comment, $article);
            $this->addFlash(
                'notice',
                'You added commentary to article "'.$article->getTitle().'"'
            );
        }

        return $this->render('index/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/{id}/regard/like", name="article_like", methods={"PUT"})
     */
    public function articleLike(Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::REGARD, $article);
        $ratingValue = $this->articleService->toggleRegardArticle($article, Regard::LIKE);

        return new Response($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/regard/dislike", name="article_dislike", methods={"PUT"})
     */
    public function articleDislike(Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::REGARD, $article);
        $ratingValue = $this->articleService->toggleRegardArticle($article, Regard::DISLIKE);

        return new Response($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/tags", name="user_article_tags")
     */
    public function userArticleTags(Article $article): JsonResponse
    {
        return new JsonResponse($this->articleService->getArticleTags($article), Response::HTTP_OK);
    }
}
