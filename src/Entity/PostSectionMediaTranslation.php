<?php

namespace App\Entity;

use App\Entity\Traits\MediaAccessibilityProperties;
use App\Repository\MediaTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MediaTranslationRepository::class)]
class PostSectionMediaTranslation
{
    use TimestampableEntity;
    use MediaAccessibilityProperties;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PostSectionMedia $media = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getMedia(): ?PostSectionMedia
    {
        return $this->media;
    }

    public function setMedia(?PostSectionMedia $media): static
    {
        $this->media = $media;

        return $this;
    }
}
