<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    public const COUNT_ON_PAGE = 10;

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const STATUS_NOT_VERIFIED = 'Not verified';
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_BLOCKED = 'Blocked';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email
     * @Assert\NotBlank(
     *      message = "Email cannot be empty"
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Your email must be at least {{ limit }} characters long",
     *      maxMessage = "Your email cannot be longer than {{ limit }} characters"
     * )
     */
    private $email;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      message = "Email cannot be empty"
     * )
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Your full name must be at least {{ limit }} characters long",
     *      maxMessage = "Your full name cannot be longer than {{ limit }} characters"
     * )
     */
    private $fullName;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="author", orphanRemoval=true)
     */
    private $articles;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=4096, nullable=true)
     * @Assert\NotBlank(message = "Please enter a password")
     * @Assert\Length(
     *      min = 6,
     *      max = 4096,
     *      minMessage = "Your password should be at least {{ limit }} characters",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters",
     * )
     */
    private $password;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = User::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // $this->plainPassword = null;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Article[]|Collection
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    public function getRating(): int
    {
        $rating = 0;
        foreach ($this->articles as $article) {
            $rating += $article->getRating();
        }

        return $rating;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isActive(): bool
    {
        return self::STATUS_ACTIVE === $this->getStatus();
    }

    public function isBlocked(): bool
    {
        return self::STATUS_BLOCKED === $this->getStatus();
    }
}
