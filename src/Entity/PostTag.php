<?php

namespace App\Entity;

use App\Entity\Contracts\TranslatableEntityInterface;
use App\Repository\PostTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTagRepository::class)]
class PostTag implements TranslatableEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @var Collection<int, PostTagTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: PostTagTranslation::class,
        mappedBy: 'postTag',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    )]
    private Collection $translations;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    #[ORM\Column(length: 255)]
    private string $color = '#000000';

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getAdminName(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->getTranslation('fr')?->getName() ?? 'Unnamed Tag';
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getTranslation(string $locale): ?PostTagTranslation
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
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

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
