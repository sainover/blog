<?php

namespace App\Service;

use App\Entity\Comment;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;
use App\Entity\Article;
use App\Service\ArticleService;

class CommentService
{
    private $manager;
    private $currentUser;
    private $articleService;
    
    public function __construct(ObjectManager $manager, Security $security, ArticleService $articleService)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->articleService = $articleService;
    }

    public function createComment(Comment $comment, Article $article): void
    {
        $comment->setAuthor($this->currentUser);
        $comment->setTarget($article);

        $article->addComment($comment);
        $this->articleService->updateArticle($article);
    }
}