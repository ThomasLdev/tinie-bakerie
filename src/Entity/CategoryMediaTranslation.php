<?php

namespace App\Entity;

use App\Entity\Traits\LocalizedEntity;
use App\Entity\Traits\MediaAccessibilityProperties;
use App\Repository\CategoryMediaTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CategoryMediaTranslationRepository::class)]
class CategoryMediaTranslation
{
    use TimestampableEntity;
    use LocalizedEntity;
    use MediaAccessibilityProperties;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CategoryMedia $categoryMedia = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryMedia(): ?CategoryMedia
    {
        return $this->categoryMedia;
    }

    public function setCategoryMedia(?CategoryMedia $categoryMedia): static
    {
        $this->categoryMedia = $categoryMedia;

        return $this;
    }
}
