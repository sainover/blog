<?php

namespace App\Service;

use App\Entity\Regard;
use App\Entity\Article;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;
use App\Service\ArticleService;

class RegardService
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

    public function toggleRegardArticle(Article $article, bool $value): int
    {
        $regard = $this->manager
            ->getRepository(Regard::class)
            ->findOneBy(["author" => $this->currentUser, "target" => $article]);
        
        if (null === $regard) {
            $regard = new Regard;
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

        return $this->articleService()->updateRating($article);
    }
}