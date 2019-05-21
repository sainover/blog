<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use App\Service\BlogService;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private $blogService;

    public function __contruct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    /**
     * @Route("/", defaults={"page": "1"}, name="index", methods={"GET"})
     */
    public function index(Request $request, int $page, PostRepository $postRepository, TagRepository $tagRepository): Response
    {
        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $tagRepository->findOneBy(['name' => $request->query->get('tag')]);
        }
        $posts = $postRepository->findLatest($page, $tag);
        return $this->render('index/index.html.twig', [
            'thisPage' => $page,
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_show", methods={"GET"})
     */
    public function postShow(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => '$post',
        ]);
    }
}
