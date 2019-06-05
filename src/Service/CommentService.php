<?php

namespace App\Service;

use App\Entity\Comment;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;
use App\Entity\Article;

class CommentService
{
    private $manager;
    private $currentUser;
    
    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
    }

    public function createComment(Comment $comment, Article $article): void
    {
        $comment->setAuthor($this->currentUser);
        $comment->setTarget($article);
        $this->manager->persist($comment);
        $this->manager->flush();
    }
}