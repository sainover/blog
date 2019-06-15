<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\Comment;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class CommentService
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    public function create(Comment $comment, Article $article): void
    {
        $user = $this->security->getUser();

        $comment->setAuthor($user);
        $comment->setTarget($article);

        $article->addComment($comment);

        $this->manager->persist($article);
        $this->manager->flush();
    }
}
