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
    #[ORM\OneToMany(targetEntity: PostTranslation::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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
}
