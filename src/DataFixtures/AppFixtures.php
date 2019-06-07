<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Regard;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTags($manager);
        $this->loadArticles($manager);
    }

    public function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$email, $fullname, $password, $roles, $status, $token]) {
            $user = new User();
            $user->setEmail($email)
                ->setFullName($fullname)
                ->setPassword($this->passwordEncoder->encodePassword($user, $password))
                ->setRoles($roles)
                ->setStatus($status)
                ->setToken($token)
            ;
            $manager->persist($user);
            $this->addReference($email, $user);
        }

        $manager->flush();
    }

    public function loadTags(ObjectManager $manager)
    {
        foreach ($this->getTagData() as $name) {
            $tag = new Tag();
            $tag->setName($name);

            $manager->persist($tag);
            $this->addReference('tag-'.$name, $tag);
        }

        $manager->flush();
    }

    public function loadArticles(ObjectManager $manager)
    {
        foreach ($this->getArticleData() as [$title, $content, $publishedAt, $author, $tags, $rating, $status]) {
            $article = new Article();
            $article->setTitle($title);
            $article->setContent($content);
            $article->setPublishedAt($publishedAt);
            $article->setAuthor($author);
            $article->addTag(...$tags);
            $article->setRating($rating);
            $article->setStatus($status);

            $emails = $this->getUserEmails();

            foreach ($emails as $idx => $email) {
                $email = $emails[array_rand($emails)];

                $comment = new Comment();
                $comment->setAuthor($this->getReference($email));
                $comment->setContent($this->getRandomText(random_int(100, 255)));
                $comment->setPublishedAt(new \DateTime('now + '.$idx.'seconds'));

                $article->addComment($comment);

                $regard = new Regard();
                $regard->setAuthor($this->getReference($email));
                $regard->setValue(1 === random_int(0, 1) ? Regard::LIKE : Regard::DISLIKE);

                $article->addRegard($regard);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            // [$email, $fullname, $password, $roles, $status, $token]
            ['admin@admin.com', 'Administrator', '12345678', [User::ROLE_ADMIN], User::STATUS_ACTIVE, ''],
            ['john@noveo.com', 'John Doe', '12345678', [User::ROLE_USER], User::STATUS_ACTIVE, ''],
            ['jane@noveo.com', 'Jane Doe', '12345678', [User::ROLE_USER], User::STATUS_BLOCKED, ''],
            ['ivan@noveo.com', 'Ivan Dorn', '12345678', [User::ROLE_USER], User::STATUS_ACTIVE, ''],
        ];
    }

    private function getUserEmails(): array
    {
        $emails = [];
        foreach ($this->getUserData() as [$email]) {
            $emails[] = $email;
        }

        return $emails;
    }

    private function getTagData(): array
    {
        return ['Symfony',
            'PHP',
            'Python',
            'HTML',
            'JavaScript',
            'Ruby',
            'NodeJS',
            'JS',
            'MySQL',
            'NoSQL',
        ];
    }

    private function getArticleData(): array
    {
        $emails = $this->getUserEmails();
        $statuses = Article::STATUSES_ALL;

        $articles = [];
        foreach ($this->getPhrases() as $title) {
            $randEmail = $emails[array_rand($emails)];
            $randStatus = $statuses[array_rand($statuses)];

            $articles[] = [
                $title,
                $this->getRandomText(),
                new \DateTime('now'),
                $this->getReference($randEmail),
                $this->getRandomTags(),
                0,
                $randStatus,
            ];
        }

        return $articles;
    }

    private function getRandomTags(): array
    {
        $tagNames = $this->getTagData();
        shuffle($tagNames);
        $selectedTags = \array_slice($tagNames, 0, random_int(2, 4));

        return array_map(function ($tagName) { return $this->getReference('tag-'.$tagName); }, $selectedTags);
    }

    private function getPhrases(): array
    {
        return [
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes de placidus vita',
            'Ubi est audax amicitia',
            'Eposs sunt solems de superbus fortis',
            'Vae humani generis',
            'Diatrias tolerare tanquam noster caesium',
            'Teres talis saepe tractare de camerarius flavum sensorem',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre talis camerarius fluctuies',
            'Era brevis ratione est',
            'Sunt torquises imitari velox mirabilis medicinaes',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss virtualiter transferre de flavum',
        ];
    }

    private function getRandomText(int $maxLength = 500): string
    {
        $phrases = $this->getPhrases();
        shuffle($phrases);

        while (mb_strlen($text = implode('. ', $phrases).'.') > $maxLength) {
            array_pop($phrases);
        }

        return $text;
    }
}
