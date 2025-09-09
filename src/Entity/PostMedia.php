<?php

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Contracts\MediaEntityInterface;
use App\Services\Media\Enum\MediaType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity]
#[Vich\Uploadable]
class PostMedia implements LocalizedEntityInterface, MediaEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: true)]
    private ?string $mediaName = null;

    #[Vich\UploadableField(mapping: 'post_media', fileNameProperty: 'mediaName')]
    private ?File $mediaFile = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Post $post;

    #[ORM\Column(enumType: MediaType::class)]
    private MediaType $type;

    /**
     * @var Collection<int, PostMediaTranslation>
     */
    #[ORM\OneToMany(targetEntity: PostMediaTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
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

    public function setMediaName(?string $mediaName): PostMedia
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function setMediaFile(?File $mediaFile = null): PostMedia
    {
        $this->mediaFile = $mediaFile;

        if (null !== $mediaFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTime();
        }

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): PostMedia
    {
        $this->post = $post;

        return $this;
    }

    public function getType(): MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): PostMedia
    {
        $this->type = $type;

        return $this;
    }

    public function setTranslations(ArrayCollection|iterable $translations): PostMedia
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
     * @return Collection<int,PostMediaTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param PostMediaTranslation $translation
     */
    public function addTranslation(EntityTranslation $translation): PostMedia
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
     * With the locale filter enabled, there is only one translation in the collection
     */
    private function getLocalizedTranslation(): ?PostMediaTranslation
    {
        $translations = $this->getTranslations()->first();

        return false === $translations ? null : $translations;
    }
}
