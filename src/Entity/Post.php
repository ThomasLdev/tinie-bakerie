<?php

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Contracts\HasSluggableTranslation;
use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Traits\ActivableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class Post implements LocalizedEntityInterface, HasSluggableTranslation
{
    use TimestampableEntity;
    use ActivableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(
        targetEntity: Tag::class,
        inversedBy: 'posts',
        cascade: ['persist'],
        fetch: 'EXTRA_LAZY',
    )]
    private Collection $tags;

    /**
     * @var Collection<int,PostMedia>
     */
    #[ORM\OneToMany(
        targetEntity: PostMedia::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    /**
     * @var Collection<int,PostSection>
     */
    #[ORM\OneToMany(
        targetEntity: PostSection::class,
        mappedBy: 'post',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sections;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $readingTime = 0;

    /**
     * @var Collection<int,PostTranslation>
     */
    #[ORM\OneToMany(targetEntity: PostTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): Post
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param array<array-key,Tag> $tags
     */
    public function setTags(array $tags): Post
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }

    /**
     * @return Collection<int,Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): Post
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): Post
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @param array<array-key,PostMedia> $media
     */
    public function setMedia(array $media): Post
    {
        foreach ($media as $medium) {
            $this->addMedium($medium);
        }

        return $this;
    }

    /**
     * @return Collection<int,PostMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(PostMedia $medium): Post
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPost($this);
        }

        return $this;
    }

    public function removeMedium(PostMedia $medium): Post
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getPost() === $this) {
                $medium->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @param array<array-key,PostSection> $sections
     */
    public function setSections(array $sections): Post
    {
        foreach ($sections as $section) {
            $this->addSection($section);
        }

        return $this;
    }

    /**
     * @return Collection<int,PostSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(PostSection $section): Post
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPost($this);
        }

        return $this;
    }

    public function removeSection(PostSection $section): Post
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getPost() === $this) {
                $section->setPost(null);
            }
        }

        return $this;
    }

    public function getReadingTime(): int
    {
        return $this->readingTime;
    }

    public function setReadingTime(int $readingTime): Post
    {
        $this->readingTime = $readingTime;

        return $this;
    }

    public function setTranslations(ArrayCollection|iterable $translations): Post
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
     * @return Collection<int,PostTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param PostTranslation $translation
     */
    public function addTranslation(EntityTranslation $translation): Post
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

    public function getSlug(): string
    {
        return $this->getLocalizedTranslation()?->getSlug() ?? '';
    }

    /**
     * With the locale filter enabled, there is only one translation in the collection
     */
    private function getLocalizedTranslation(): ?PostTranslation
    {
        $translations = $this->getTranslations()->first();

        return false === $translations ? null : $translations;
    }

    public function getTranslationByLocale(string $locale): ?PostTranslation
    {
        foreach ($this->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }
}

