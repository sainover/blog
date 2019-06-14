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
     * @Route("/", name="user_index")
     */
    public function index(Request $request, ArticleRepository $articleRepository): Response
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
}
