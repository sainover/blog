<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;

class BlogService
{
    private $manager;
    
    public function __contruct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function deletePost(Post $post, Request $request)
    {
        $this->manager->remove($post);
        $this->manager->flush();
    }

    public function createPost($content, $title, User $author, $tags): Post
    {
        $post = new Post;
        $post->setContent($content);
        $post->setAuthor($author);
        $post->setTags($tags);
        $this->manager->persist($post);
        $this->manager->flush();
    }

    public function updatePost(Post $post, String $title, $content, User $author, $tags): Post
    {
        $post->setTitle($title);
        $post->setContent($content);
        $post->setAuthor($author);
        foreach($tags as $tag) {
            $post->addTag($tag);
        }
        $post->setTags($tags);
        $this->manager->flush();
    }
}