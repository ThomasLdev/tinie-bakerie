<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Contracts\LocalizedEntityInterface;
use App\Entity\Traits\LocalizedEntity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

#[ORM\Entity]
class Tag implements LocalizedEntityInterface
{
    use TimestampableEntity;
    use LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    #[ORM\Column(type: Types::STRING, options: ['default' => '#000000'])]
    private string $color = '#000000';

    #[Gedmo\Translatable]
    #[ORM\Column(type: Types::STRING)]
    private string $title;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $activatedAt = null;

    /**
     * @var Collection<int, TagTranslation>
     */
    #[ORM\OneToMany(targetEntity: TagTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private Collection $translations;

    public function __toString(): string
    {
        return $this->getTitle();
    }

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

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
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

    public function getActivatedAt(): ?DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?DateTimeImmutable $activatedAt): self
    {
        $this->activatedAt = $activatedAt;

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
