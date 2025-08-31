<?php

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Contracts\MediaEntityInterface;
use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\MediaAccessibility;
use App\Services\Media\Enum\MediaType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class PostSectionMedia implements LocalizedEntityInterface, MediaEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?string $mediaName = null;

    #[Vich\UploadableField(mapping: 'post_section_media', fileNameProperty: 'mediaName')]
    private ?File $mediaFile = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostSection $postSection;

    #[ORM\Column(enumType: MediaType::class)]
    private MediaType $type;

    /**
     * @var Collection<int, PostSectionMediaTranslation>
     */
    #[ORM\OneToMany(targetEntity: PostSectionMediaTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
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

        if (null !== $mediaFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    public function getPostSection(): ?PostSection
    {
        return $this->postSection;
    }

    public function setPostSection(?PostSection $postSection): self
    {
        $this->postSection = $postSection;

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

    /**
     * @param array<int,PostSectionMediaTranslation> $translations
     */
    public function setTranslations(array $translations): PostSectionMedia
    {
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

        return $this;
    }

    public function getTranslations(): ArrayCollection
    {
        return $this->translations;
    }

    /**
     * @param PostSectionMediaTranslation $translation
     */
    public function addTranslation(EntityTranslation $translation): PostSectionMedia
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }
}
