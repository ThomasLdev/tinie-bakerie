<?php

namespace App\Entity;

use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\MediaAccessibilityProperties;
use App\Repository\PostMediaTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostMediaTranslationRepository::class)]
class PostMediaTranslation
{
    use TimestampableEntity;
    use LocalizedEntity;
    use MediaAccessibilityProperties;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PostMedia $postMedia = null;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getPostMedia(): ?PostMedia
    {
        return $this->postMedia;
    }

    public function setPostMedia(?PostMedia $postMedia): static
    {
        $this->postMedia = $postMedia;

        return $this;
    }
}
