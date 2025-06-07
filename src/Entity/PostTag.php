<?php

namespace App\Entity;

use App\Repository\PostTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTagRepository::class)]
class PostTag
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, PostTagTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: PostTagTranslation::class,
        mappedBy: 'postTag',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $translations;

    #[ORM\ManyToOne(inversedBy: 'tags')]
    private ?Post $post = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, PostTagTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostTagTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setPostTag($this);
        }

        return $this;
    }

    public function removeTranslation(PostTagTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getPostTag() === $this) {
                $translation->setPostTag(null);
            }
        }

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }
}
