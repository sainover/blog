<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    public const COUNT_ON_PAGE = 10;

    public const STATUS_DRAFT = 'Draft';
    public const STATUS_MODERATION = 'On moderation';
    public const STATUS_PUBLISHED = 'Published';
    public const STATUS_DECLINED= 'Declined';

    public const STATUSES_ALL = [
        self::STATUS_DRAFT => self::STATUS_DRAFT,
        self::STATUS_MODERATION => self::STATUS_MODERATION,
        self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
        self::STATUS_DECLINED => self::STATUS_DECLINED,
    ];

    public const STATUSES_VIEWABLE_TO_ADMIN = [
        self::STATUS_MODERATION => self::STATUS_MODERATION,
        self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
        self::STATUS_DECLINED => self::STATUS_DECLINED,
    ];

    public const STATUSES_CHANGABLE_BY_ADMIN = [
        self::STATUS_DRAFT => self::STATUS_DRAFT,
        self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
        self::STATUS_DECLINED => self::STATUS_DECLINED,
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\Comment", 
     *      mappedBy="target", 
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="articles", cascade={"persist"})
     */
    private $tags;

    /**
     * @ORM\Column(type="integer")
     */
    private $rating;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(
     *      targetEntity="App\Entity\Regard", 
     *      mappedBy="target", 
     *      orphanRemoval=true,
     *      cascade={"persist"}
     * )
     */
    private $regards;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->regards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTarget($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getTarget() === $this) {
                $comment->setTarget(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag ...$tags): void
    {
        foreach($tags as $tag) {
            if (!$this->tags->contains($tag)) {
                $this->tags->add($tag);
            }
        }
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Regard[]
     */
    public function getRegards(): Collection
    {
        return $this->regards;
    }

    public function addRegard(Regard $regard): self
    {
        if (!$this->regards->contains($regard)) {
            $this->regards[] = $regard;
            $regard->setTarget($this);
        }

        return $this;
    }

    public function removeRegard(Regard $regard): self
    {
        if ($this->regards->contains($regard)) {
            $this->regards->removeElement($regard);
            // set the owning side to null (unless already changed)
            if ($regard->getTarget() === $this) {
                $regard->setTarget(null);
            }
        }

        return $this;
    }
}
