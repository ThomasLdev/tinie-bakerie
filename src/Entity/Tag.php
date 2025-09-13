<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\HasTranslations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @implements HasTranslations<TagTranslation>
 */
#[ORM\Entity]
class Tag implements HasTranslations, \Stringable
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /** @var Collection<int,Post> */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    #[ORM\Column(type: Types::STRING, options: ['default' => '#000000'])]
    private string $color = '#000000';

    /** @var Collection<int,TagTranslation> */
    #[ORM\OneToMany(targetEntity: TagTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param TagTranslation[] $translations
     */
    public function setTranslations(array $translations): self
    {
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

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

    public function getTitle(): string
    {
        return $this->getLocalizedTranslation()?->getTitle() ?? '';
    }

    /**
     * With the locale filter enabled, there is only one translation in the collection.
     */
    private function getLocalizedTranslation(): ?TagTranslation
    {
        $translations = $this->getTranslations()->first();

        return false === $translations ? null : $translations;
    }
}
