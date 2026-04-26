<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\Translatable;
use App\Entity\Traits\Featurable;
use App\Entity\Traits\TranslationAccessorTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JoliCode\MediaBundle\Doctrine\Types as JoliMediaTypes;
use JoliCode\MediaBundle\Model\Media;

/**
 * @implements Translatable<TagTranslation>
 */
#[ORM\Entity]
class Tag implements Translatable, \Stringable
{
    use Featurable;
    use TimestampableEntity;

    /** @use TranslationAccessorTrait<TagTranslation> */
    use TranslationAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /** @var Collection<int,Post> */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    #[ORM\Column(type: JoliMediaTypes::MEDIA, nullable: true)]
    private ?Media $image = null;

    /** @var Collection<int,TagTranslation> */
    #[ORM\OneToMany(
        targetEntity: TagTranslation::class,
        mappedBy: 'translatable',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $translations;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
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

    /**
     * @return Collection<int,Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getImage(): ?Media
    {
        return $this->image;
    }

    public function setImage(?Media $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int,TagTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TagTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(TagTranslation $translation): self
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * Returns the translation for the current locale with the specific type.
     * Uses covariant return type to narrow Translation to TagTranslation.
     */
    public function getCurrentTranslation(): ?TagTranslation
    {
        $translation = $this->getTranslationForCurrentLocale();

        return $translation instanceof TagTranslation ? $translation : null;
    }

    public function getTitle(): string
    {
        return $this->getCurrentTranslation()?->getTitle() ?? '';
    }
}
