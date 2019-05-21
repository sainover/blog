<?php

namespace App\Controller;

use App\Entity\Post;
use App\Service\BlogService;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user/post")
 */
class UserController extends AbstractController
{
    private $blogService;

    public function __contruct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    /**
     * @Route("/", name="user_post_index")
     */
    public function userPostIndex(PostRepository $post)
    {
        $authorPosts = $this->blogService->getAuthorPosts($this->getUser);

        return $this->render('user/index.html.twig', [
            'posts' => $authorPosts
        ]);
    }

    /**
     * @Route("/new", name="user_post_new", methods={"POST"})
     */
    public function userPostNew(Request $request)
    {
        $form = $this->createForm(PostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogService->createPost(
                $form->get('title'),
                $form->get('content'),
                $this->getUser(),
                $form->get('tags')
            );
        }

        return $this->redirectToRoute('user_post_index');
    }

    /**
     * @Route("/{id}/edit", name="user_post_edit", methods={"PUT"})
     */
    public function userEditPost(Post $post, Request $request)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogService->updatePost(
                $post,
                $form->get('title'),
                $form->get('content'),
                $this->getUser(),
                $form->get('tags')
            );

            return $this->redirectToRoute('user_post_index');
        }

        return $this->render('user/edit.html.twig', [
            'form'=> $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="user_post_delete", methods={"DELETE"})
     */
    public function userDeletePost(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->blogService->deletePost($post, $request);
            return $this->redirectToRoute("user_post_index");
        }
    }
}
