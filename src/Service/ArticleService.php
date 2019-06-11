<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\Regard;
use App\Repository\RegardRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class ArticleService
{
    private $manager;
    private $regardRepository;
    private $security;

    public function __construct(
        ObjectManager $manager,
        Security $security,
        RegardRepository $regardRepository
    ) {
        $this->manager = $manager;
        $this->security = $security;
        $this->regardRepository = $regardRepository;
    }

    public function setArticleStatus(Article $article, string $status): void
    {
        $article->setStatus($status);
        if (Article::STATUS_PUBLISHED === $status) {
            $article->setPublishedAt(new \DateTime());
        }
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function sendToModeration(Article $article): void
    {
        $article->setStatus(Article::STATUS_MODERATION);
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function deleteArticle(Article $article): void
    {
        $this->manager->remove($article);
        $this->manager->flush();
    }

    public function createArticle(Article $article): void
    {
        $user = $this->security->getUser();

        $article->setAuthor($user);
        $article->setStatus(Article::STATUS_DRAFT);
        $article->setRating(0);
        $this->manager->persist($article);
        $this->manager->flush();
    }

    public function toggleRegardArticle(Article $article, bool $value): int
    {
        $user = $this->security->getUser();

        $regard = $this->regardRepository->findByAuthorAndTarget($user, $article);

        if (null === $regard) {
            $regard = new Regard();
            $regard
                ->setAuthor($user)
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
            $data['results'][] = [
                'item' => $tag->getId(),
                'text' => $tag->getName(),
            ];
        }

        return $data;
    }
}
