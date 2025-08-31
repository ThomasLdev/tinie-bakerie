<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\EntityTranslation;
use App\Entity\Contracts\LocalizedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class Tag implements LocalizedEntityInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @var Collection<int,Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    #[ORM\Column(type: Types::STRING, options: ['default' => '#000000'])]
    private string $color = '#000000';

    /**
     * @var Collection<int, TagTranslation>
     */
    #[ORM\OneToMany(targetEntity: TagTranslation::class, mappedBy: 'translatable', cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): Tag
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param array<int,TagTranslation> $translations
     */
    public function setTranslations(array $translations): Tag
    {
        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }

        return $this;
    }

    /**
     * @return Collection<int, TagTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    /**
     * @param TagTranslation $translation
     */
    public function addTranslation(EntityTranslation $translation): Tag
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getLocalizedTranslation()->getTitle();
    }

    /**
     * With the locale filter enabled, there is only one translation in the collection
     */
    private function getLocalizedTranslation(): TagTranslation
    {
        return $this->getTranslations()->first();
    }
}
