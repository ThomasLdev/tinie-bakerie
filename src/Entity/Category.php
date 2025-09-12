<?php

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Contracts\HasSluggableTranslation;
use App\Entity\Contracts\LocalizedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class Category implements LocalizedEntityInterface, HasSluggableTranslation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(
        targetEntity: Post::class,
        mappedBy: 'category',
        fetch: 'EXTRA_LAZY',
    )]
    private Collection $posts;

    /**
     * @var Collection<int,CategoryMedia>
     */
    #[ORM\OneToMany(
        targetEntity: CategoryMedia::class,
        mappedBy: 'category',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    /**
     * @var Collection<int,CategoryTranslation>
     */
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
     * @return Collection<int, Post>
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
     * @param array<array-key,CategoryMedia> $media
     */
    public function setMedia(array $media): Category
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

    public function addMedium(CategoryMedia $medium): Category
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setCategory($this);
        }

        return $this;
    }

    public function removeMedium(CategoryMedia $medium): Category
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getCategory() === $this) {
                $medium->setCategory(null);
            }
        }

        return $this;
    }

    public function setTranslations(ArrayCollection|iterable $translations): Category
    {
        if (is_array($translations)) {
            $translations = new ArrayCollection($translations);
        }

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

    /**
     * @param CategoryTranslation $translation
     */
    public function addTranslation(EntityTranslation $translation): Category
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getLocalizedTranslation()?->getTitle() ?? '';
    }

    public function getDescription(): string
    {
        return $this->getLocalizedTranslation()?->getDescription() ?? '';
    }

    public function getSlug(): string
    {
        return $this->getLocalizedTranslation()?->getSlug() ?? '';
    }

    public function getMetaTitle(): string
    {
        return $this->getLocalizedTranslation()?->getMetaTitle() ?? '';
    }

    public function getMetaDescription(): string
    {
        return $this->getLocalizedTranslation()?->getMetaDescription() ?? '';
    }

    public function getExcerpt(): string
    {
        return $this->getLocalizedTranslation()?->getExcerpt() ?? '';
    }

    /**
     * With the locale filter enabled, there is only one translation in the collection
     */
    private function getLocalizedTranslation(): ?CategoryTranslation
    {
        $translations = $this->getTranslations()->first();

        return false === $translations ? null : $translations;
    }

    public function getTranslationByLocale(string $locale): ?CategoryTranslation
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }
}
