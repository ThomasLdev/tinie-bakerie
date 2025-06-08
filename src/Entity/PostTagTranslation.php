<?php

namespace App\Entity;

use App\Entity\Trait\LocalizedEntity;
use App\Repository\PostTagTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PostTagTranslationRepository::class)]
class PostTagTranslation
{
    use TimestampableEntity, LocalizedEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PostTag $postTag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPostTag(): ?PostTag
    {
        return $this->postTag;
    }

    public function setPostTag(?PostTag $postTag): static
    {
        $this->postTag = $postTag;

        return $this;
    }
}
