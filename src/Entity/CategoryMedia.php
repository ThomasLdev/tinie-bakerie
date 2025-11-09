<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\HasMediaEntities;
use App\Entity\Contracts\Translatable;
use App\Services\Media\Enum\MediaType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @implements Translatable<CategoryMediaTranslation>
 */
#[ORM\Entity]
#[Vich\Uploadable]
class CategoryMedia implements Translatable, HasMediaEntities, \Stringable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?string $mediaName = null;

    #[Vich\UploadableField(mapping: 'category_media', fileNameProperty: 'mediaName')]
    private ?File $mediaFile = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(enumType: MediaType::class)]
    private MediaType $type;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $position = 0;

    /** @var Collection<int,CategoryMediaTranslation> */
    #[ORM\OneToMany(
        targetEntity: CategoryMediaTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
    )
    ]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getMediaName() ?? '';
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaName(?string $mediaName): self
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function setMediaFile(?File $mediaFile = null): self
    {
        $this->mediaFile = $mediaFile;

        if ($mediaFile instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param iterable<CategoryMediaTranslation> $translations
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
     * @return Collection<int,CategoryMediaTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(CategoryMediaTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function getAlt(): string
    {
        return $this->getLocalizedTranslation()?->getAlt() ?? '';
    }

    public function getTitle(): string
    {
        return $this->getLocalizedTranslation()?->getTitle() ?? '';
    }

    /**
     * With the locale filter enabled, there is only one translation in the collection.
     */
    private function getLocalizedTranslation(): ?CategoryMediaTranslation
    {
        $translations = $this->getTranslations()->first();

        return false === $translations ? null : $translations;
    }
}
