<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    /**
     * @var Collection<int, PostTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: PostTranslation::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $translations;

    /**
     * @var Collection<int, PostTag>
     */
    #[ORM\OneToMany(targetEntity: PostTag::class, mappedBy: 'post')]
    private Collection $tags;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, PostTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostTranslation $translations): static
    {
        if (!$this->translations->contains($translations)) {
            $this->translations->add($translations);
            $translations->setPost($this);
        }

        return $this;
    }

    public function removePostTranslation(PostTranslation $translations): static
    {
        if ($this->translations->removeElement($translations)) {
            // set the owning side to null (unless already changed)
            if ($translations->getPost() === $this) {
                $translations->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(PostTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setPost($this);
        }

        return $this;
    }

    public function removeTag(PostTag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getPost() === $this) {
                $tag->setPost(null);
            }
        }

        return $this;
    }
}
