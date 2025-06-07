<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    private ?Category $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    /**
     * @var Collection<int, CategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $translations;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'category')]
    private Collection $posts;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, CategoryTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(CategoryTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setCategory($this);
        }

        return $this;
    }

    public function removeTranslation(CategoryTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getCategory() === $this) {
                $translation->setCategory(null);
            }
        }

        return $this;
    }

    public function getParent(): ?Category
    {
        return $this->parent;
    }

    public function setParent(?Category $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
