<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use App\Entity\Regard;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class ArticleService
{
    private $manager;
    private $currentUser;
    
    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
    }

    public function setArticleStatus(Article $article, string $status): void
    {
        if (in_array(User::ROLE_ADMIN, $this->currentUser->getRoles())) {
            $article->setStatus($status);
            $this->updateArticle($article);
        }
    }

    public function sendToModeration(Article $article): void
    {
        if (in_array(User::ROLE_USER, $this->currentUser->getRoles())) {
            $article->setStatus(Article::STATUS_MODERATION);
            $this->updateArticle($article);
        }
    }

    public function deleteArticle(Article $article): void
    {
        $this->manager->remove($article);
        $this->manager->flush();
    }

    public function createArticle(Article $article): void
    {
        $article->setAuthor($this->currentUser);
        $article->setStatus(Article::STATUS_DRAFT);
        $article->setRating(0);
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function updateArticle(Article $article): void
    {
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function getAuthorArticles(User $user): Array
    {
        $articles = $this->manager
            ->getRepository(Article::class)
            ->findBy(["author" => $user])
        ;
        return $articles;
    }

    public function updateRating(Article $article): int
    {
        $regards = $this->manager
            ->getRepository(Regard::class)
            ->findBy(["target" => $article])
        ;

        $rating = 0;
        
        foreach($regards as $regard) {
            $rating += $regard->getValue() === Regard::LIKE ? 1 : -1;
        }

        $article->setRating($rating);

        $this->manager->persist($article);
        $this->manager->flush();

        return $rating;
    }

    public function isEditableArticle($article): bool
    {
        return $article->getStatus() === Article::STATUS_DRAFT;
    }

    public function isViewableArticle($article): bool
    {
        return $article->getStatus() === Article::STATUS_PUBLISHED;
    }

    public function isAuthorArticle($user, $article): bool
    {
        return $article->getAuthor() === $user;
    }

    public function isCommentable($article): bool
    {
        return $this->isViewableArticle($article);
    }

    public function getArticleTagsAsArray($article): array
    {
        $data['results'] = [];
        
        $articleTags = $article->getTags();

        foreach($articleTags as $tag) {
            $item['id'] = $tag->getId();
            $item['text'] = $tag->getName();
            $data['results'][] = $item;
        }

        return $data;
    }
}