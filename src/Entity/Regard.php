<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RegardRepository")
 */
class Regard
{
    public const LIKE = true;
    public const DISLIKE = false;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="regards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $target;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTarget(): ?Article
    {
        return $this->target;
    }

    public function setTarget(?Article $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getValue(): ?bool
    {
        return $this->value;
    }

    public function setValue(bool $value): self
    {
        $this->value = $value;

        return $this;
    }
}
