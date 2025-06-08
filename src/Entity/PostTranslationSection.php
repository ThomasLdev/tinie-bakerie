<?php

namespace App\Entity;

use App\Repository\PostTranslationSectionRepository;
use App\Services\PostTranslation\Enum\PostTranslationSectionType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTranslationSectionRepository::class)]
class PostTranslationSection
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PostTranslation $translation = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column(
        type: 'string',
        enumType: PostTranslationSectionType::class,
        options: ['default' => PostTranslationSectionType::TextPlain, 'nullable' => false]
    )]
    private PostTranslationSectionType $type;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PostTranslationSectionMedia $media = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getTranslation(): ?PostTranslation
    {
        return $this->translation;
    }

    public function setTranslation(?PostTranslation $translation): static
    {
        $this->translation = $translation;

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

    public function getType(): PostTranslationSectionType
    {
        return $this->type;
    }

    public function setType(PostTranslationSectionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMedia(): ?PostTranslationSectionMedia
    {
        return $this->media;
    }

    public function setMedia(?PostTranslationSectionMedia $media): static
    {
        $this->media = $media;

        return $this;
    }
}
