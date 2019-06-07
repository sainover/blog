<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\Regard;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;
use App\Repository\RegardRepository;

class ArticleService
{
    private $manager;
    private $currentUser;
    private $regardRepository;

    public function __construct(
        ObjectManager $manager, 
        Security $security,
        RegardRepository $regardRepository
    ) {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->regardRepository = $regardRepository;
    }

    public function setArticleStatus(Article $article, string $status): void
    {
        $article->setStatus($status);
        $this->updateArticle($article);
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

    public function toggleRegardArticle(Article $article, bool $value): int
    {
        $regard = $this->regardRepository->findByAuthorAndTarget($this->currentUser, $article);

        if (null === $regard) {
            $regard = new Regard();
            $regard
                ->setAuthor($this->currentUser)
                ->setTarget($article)
            ;
        }

        if ($regard->getValue() === $value) {
            $this->manager->remove($regard);
        } else {
            $regard->setValue($value);
            $this->manager->persist($regard);
        }

        $this->manager->flush();

        return $article->getRating();
    }

    public function getArticleTagsAsArray(Article $article): array
    {
        $data['results'] = [];

        $articleTags = $article->getTags();

        foreach ($articleTags as $tag) {
            $item['id'] = $tag->getId();
            $item['text'] = $tag->getName();
            $data['results'][] = $item;
        }

        return $data;
    }
}
