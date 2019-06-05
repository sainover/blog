<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Service\ArticleService;

class CommentVoter extends Voter
{
    public const CREATE = 'create';

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof Article && \in_array($attribute, [self::DELETE, self::EDIT, self::SHOW], true);
    }

    protected function voteOnAttribute($attribute, $article, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        switch ($attribute) {
            case self::CREATE:
                return $this->canCREATE($article, $user);
                break;
        }
    }

    public function canCreate(Article $article, ?User $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        return $this->articleService->isCommentable($article);
    }
}