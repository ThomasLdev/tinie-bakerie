<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translatable;
use App\Entity\Traits\TranslationAccessorTrait;
use App\Services\PostSection\Enum\PostSectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements Translatable<PostSectionTranslation>
 */
#[ORM\Entity]
class PostSection implements Translatable, \Stringable
{
    /** @use TranslationAccessorTrait<PostSectionTranslation> */
    use TranslationAccessorTrait;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn]
    private ?Post $post = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(
        type: 'string',
        enumType: PostSectionType::class,
        options: ['default' => PostSectionType::Default, 'nullable' => false],
    )]
    private PostSectionType $type = PostSectionType::Default;

    /** @var Collection<int,PostSectionMedia> */
    #[ORM\OneToMany(
        targetEntity: PostSectionMedia::class,
        mappedBy: 'postSection',
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
    )]
    private Collection $media;

    /** @var Collection<int,PostSectionTranslation> */
    #[ORM\OneToMany(targetEntity: PostSectionTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getType(): PostSectionType
    {
        return $this->type;
    }

    public function setType(PostSectionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param PostSectionMedia[] $media
     */
    public function setMedia(array $media): self
    {
        foreach ($media as $medium) {
            $this->addMedium($medium);
        }

        return $this;
    }

    /**
     * @return Collection<int,PostSectionMedia>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(PostSectionMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setPostSection($this);
        }

        return $this;
    }

    public function removeMedium(PostSectionMedia $medium): self
    {
        // set the owning side to null (unless already changed)
        if ($this->media->removeElement($medium) && $medium->getPostSection() === $this) {
            $medium->setPostSection(null);
        }

        return $this;
    }

    /**
     * @param iterable<PostSectionTranslation> $translations
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
     * @return Collection<int,PostSectionTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PostSectionTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    /**
     * Returns the translation for the current locale with the specific type.
     * Uses covariant return type to narrow Translation to PostSectionTranslation.
     */
    public function getCurrentTranslation(): ?PostSectionTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof PostSectionTranslation ? $translation : null;
    }

    public function getTitle(): string
    {
        return $this->getCurrentTranslation()?->getTitle() ?? '';
    }

    public function getContent(): string
    {
        return $this->getCurrentTranslation()?->getContent() ?? '';
    }
}
