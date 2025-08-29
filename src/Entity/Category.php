<?php

namespace App\Entity;

use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Contracts\SluggableEntityInterface;
use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\SluggableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[Gedmo\TranslationEntity(class: CategoryTranslation::class)]
#[ORM\UniqueConstraint('category_title_unique', ['title'])]
#[ORM\Entity]
class Category implements LocalizedEntityInterface, SluggableEntityInterface
{
    use TimestampableEntity;
    use LocalizedEntity;
    use SluggableEntity;

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

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING, options: ['default' => ''])]
    private string $description = '';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $metaDescription = '';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING, length: 60, options: ['default' => ''])]
    private string $metaTitle = '';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::TEXT, options: ['default' => ''])]
    private string $excerpt = '';

    /**
     * @var Collection<int, CategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private Collection $translations;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param array<array-key,CategoryMedia> $media
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
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getCategory() === $this) {
                $medium->setCategory(null);
            }
        }

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    public function addTranslation(AbstractPersonalTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }

        return $this;
    }
}
