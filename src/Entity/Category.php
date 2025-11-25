<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translatable;
use App\Entity\Traits\TranslationAccessorTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translatable<CategoryTranslation>
 */
#[ORM\Entity]
class Category implements Translatable
{
    use TimestampableEntity;

    /** @use TranslationAccessorTrait<CategoryTranslation> */
    use TranslationAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /** @var Collection<int,Post> */
    #[ORM\OneToMany(
        targetEntity: Post::class,
        mappedBy: 'category',
        fetch: 'EXTRA_LAZY',
    )]
    private Collection $posts;

    /** @var Collection<int,CategoryMedia> */
    #[ORM\OneToMany(
        targetEntity: CategoryMedia::class,
        mappedBy: 'category',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    /** @var Collection<int,CategoryTranslation> */
    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
    private Collection $translations;

    private int $postCount = 0;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    /**
     * @return Collection<int,Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getPostCount(): int
    {
        return $this->postCount;
    }

    /**
     * @param CategoryMedia[] $media
     */
    public function setMedia(array $media): self
    {
        foreach ($media as $medium) {
            $this->addMedium($medium);
        }

        return $this;
    }

    /**
     * @return Collection<int,CategoryMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(CategoryMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setCategory($this);
        }

        return $this;
    }

    public function removeMedium(CategoryMedia $medium): self
    {
        // set the owning side to null (unless already changed)
        if ($this->media->removeElement($medium) && $medium->getCategory() === $this) {
            $medium->setCategory(null);
        }

        return $this;
    }

    /**
     * @param iterable<CategoryTranslation> $translations
     */
    public function setTranslations(iterable $translations): self
    {
        $this->translations->clear();

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

        return $this;
    }

    /**
     * @return Collection<int,CategoryTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(CategoryTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Returns the translation for the current locale with the specific type.
     * Uses covariant return type to narrow Translation to CategoryTranslation.
     */
    public function getCurrentTranslation(): ?CategoryTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof CategoryTranslation ? $translation : null;
    }

    public function getTitle(): string
    {
        return $this->getCurrentTranslation()?->getTitle() ?? '';
    }

    public function getDescription(): string
    {
        return $this->getCurrentTranslation()?->getDescription() ?? '';
    }

    public function getSlug(): string
    {
        return $this->getCurrentTranslation()?->getSlug() ?? '';
    }

    public function getMetaTitle(): string
    {
        return $this->getCurrentTranslation()?->getMetaTitle() ?? '';
    }

    public function getMetaDescription(): string
    {
        return $this->getCurrentTranslation()?->getMetaDescription() ?? '';
    }

    public function getExcerpt(): string
    {
        return $this->getCurrentTranslation()?->getExcerpt() ?? '';
    }
}
