<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Regard;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class RatingUpdater implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function postPerist(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function index(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Regard) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $article = $entity->getTarget();

        $article->setRating($this->newRating($article));
        $entityManager->persist($article);
        $entityManager->flush();
    }

    public function newRating($article): int
    {
        $regards = $article->getRegards();

        $rating = 0;
        foreach ($regards as $regard) {
            $rating += Regard::LIKE === $regard->getValue() ? 1 : -1;
        }

        return $rating;
    }
}
