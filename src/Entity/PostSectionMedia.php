<?php

namespace App\Entity;

use App\Entity\Contracts\TranslatableEntityInterface;
use App\Repository\MediaRepository;
use App\Services\Media\Enum\MediaType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Vich\Uploadable]
class PostSectionMedia implements TranslatableEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Vich\UploadableField(mapping: 'post_section_media', fileNameProperty: 'mediaName')]
    private ?File $mediaFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $mediaName = null;

    /**
     * @var Collection<int, PostSectionMediaTranslation>
     */
    #[ORM\OneToMany(targetEntity: PostSectionMediaTranslation::class, mappedBy: 'media', orphanRemoval: true)]
    private Collection $translations;

    #[ORM\Column(
        type: 'string',
        enumType: MediaType::class,
        options: ['default' => MediaType::Image, 'nullable' => false]
    )]
    private MediaType $mediaType;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function setMediaFile(?File $imageFile = null): static
    {
        $this->mediaFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }

        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaName(?string $mediaName): static
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    /**
     * @return Collection<int, PostSectionMediaTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostSectionMediaTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setMedia($this);
        }

        return $this;
    }

    public function removeTranslation(PostSectionMediaTranslation $translation): static
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getMedia() === $this) {
                $translation->setMedia(null);
            }
        }

        return $this;
    }

    public function getMediaType(): MediaType
    {
        return $this->mediaType;
    }

    public function setMediaType(MediaType $mediaType): static
    {
        $this->mediaType = $mediaType;

        return $this;
    }
}
