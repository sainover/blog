<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const COMMENT = 'comment';

    private const ACTIONS = [self::DELETE, self::EDIT, self::COMMENT];

    protected function supports($attributes, $subject): bool
    {
        return $subject instanceof Article && \in_array($attributes, self::ACTIONS, true);
    }

    protected function voteOnAttribute($attribute, $article, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($article, $user);
                break;
            case self::DELETE:
                return $this->canDelete($article, $user);
                break;
            case self::COMMENT:
                return $this->canComment($article, $user);
                break;
        }

        throw new \LogicException('This code should not be reached!');
    }

    public function canEdit(Article $article, User $user): bool
    {
        return $article->isAuthor($user) && $article->isEditable($article);
    }

    public function canDelete(Article $article, User $user): bool
    {
        return $article->isAuthor($user) && $article->isDeletable();
    }

    public function canComment(Article $article, User $user)
    {
        return true;
    }
}
