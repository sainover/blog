<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Regard;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\ArticleService;
use App\Service\CommentService;
use App\Security\Voter\ArticleVoter;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\TagRepository;

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
     * @Route("/", defaults={"page": "1"}, name="index", methods={"GET"})
     */
    public function index(Request $request, ArticleRepository $articleRepository, TagRepository $tagRepository): Response
    {        
        $page = $request->query->get('page') ? : 1;

        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tagRepository->findOneBy(['name' => $request->query->get('tag')]);
        }

        $articles = $articleRepository->customFind($page, ['tag' => $tag, 'status' => Article::STATUS_PUBLISHED], [], ['publishedAt' => 'DESC']);
        $maxPages = ceil(count($articles) / Article::COUNT_ON_PAGE);

        return $this->render('index/index.html.twig', [
            'thisPage' => $page,
            'maxPages' => $maxPages,
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}", name="article_show", methods={"POST", "GET"})
     */
    public function articleShow(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(ArticleVoter::SHOW, $article);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->createComment($comment, $article);
            $this->addFlash(
                'success_comment',
                'You added commentary to article "' . $article->getTitle()
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
        $ratingValue = $this->articleService->toggleRegardArticle($article, Regard::LIKE);
        return new Response($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/regard/dislike", name="article_dislike", methods={"PUT"})
     */
    public function articleDislike(Article $article): Response
    {
        $ratingValue = $this->articleService->toggleRegardArticle($article, Regard::DISLIKE);
        return new Response($ratingValue, Response::HTTP_OK);
    }

    /**
     * @Route("/article/{id}/tags", name="user_article_tags", methods={"GET"})
     */
    public function userArticleTags(Article $article): JsonResponse
    {
        return new JsonResponse($this->articleService->getArticleTags($article), Response::HTTP_OK);
    }
}
