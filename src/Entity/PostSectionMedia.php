<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\MediaAttachment;
use App\Entity\Contracts\Translatable;
use App\Entity\Traits\TranslationAccessorTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JoliCode\MediaBundle\Doctrine\Types as JoliMediaTypes;
use JoliCode\MediaBundle\Model\Media;

/**
 * @implements Translatable<PostSectionMediaTranslation>
 */
#[ORM\Entity]
class PostSectionMedia implements Translatable, MediaAttachment, \Stringable
{
    use TimestampableEntity;

    /** @use TranslationAccessorTrait<PostSectionMediaTranslation> */
    use TranslationAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: JoliMediaTypes::MEDIA, nullable: true)]
    private ?Media $media = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: true)]
    private ?PostSection $postSection = null;

    /** @var Collection<int,PostSectionMediaTranslation> */
    #[ORM\OneToMany(
        targetEntity: PostSectionMediaTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
    )]
    private Collection $translations;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $position = 0;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->media?->getPath() ?? '';
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

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
     * @param iterable<PostSectionMediaTranslation> $translations
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
     * @return Collection<int,PostSectionMediaTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostSectionMediaTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Returns the translation for the current locale with the specific type.
     * Uses covariant return type to narrow Translation to PostSectionMediaTranslation.
     */
    public function getCurrentTranslation(): ?PostSectionMediaTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof PostSectionMediaTranslation ? $translation : null;
    }

    public function getAlt(): string
    {
        return $this->getCurrentTranslation()?->getAlt() ?? '';
    }

    public function getTitle(): string
    {
        return $this->getCurrentTranslation()?->getTitle() ?? '';
    }
}
